<?php

namespace Mellooh\Himari\async\task;

use Mellooh\Himari\async\AsyncExecutor;
use PDO;
use pocketmine\scheduler\AsyncTask;
use RuntimeException;
use Throwable;

class QueryTask extends AsyncTask{

    private string $pluginName;
    private int $id;
    private string $dbConfigSerialized;
    private string $sql;
    private string $paramsSerialized;
    private ?float $timeoutSeconds;

    public function __construct(string $pluginName, int $id, array $dbConfig, string $sql, array $params, ?float $timeoutSeconds){
        $this->pluginName = $pluginName;
        $this->id = $id;
        $this->dbConfigSerialized = serialize($dbConfig);
        $this->sql = $sql;
        $this->paramsSerialized = serialize($params);
        $this->timeoutSeconds = $timeoutSeconds;
    }

    public function onRun(): void{
        $dbConfig = unserialize($this->dbConfigSerialized, ["allowed_classes" => false]);
        $params = unserialize($this->paramsSerialized, ["allowed_classes" => false]);
        if(!is_array($dbConfig)){
            $dbConfig = [];
        }
        if(!is_array($params)){
            $params = [];
        }
        $driver = $dbConfig["driver"] ?? "sqlite";
        $parameters = $dbConfig["parameters"] ?? [];
        $username = $dbConfig["username"] ?? null;
        $password = $dbConfig["password"] ?? null;
        $options = $dbConfig["options"] ?? [];
        $pdo = null;
        $payload = null;
        try{
            if($driver === "mysql"){
                $host = $parameters["host"] ?? "127.0.0.1";
                $port = $parameters["port"] ?? 3306;
                $dbname = $parameters["database"] ?? "";
                $charset = $parameters["charset"] ?? "utf8mb4";
                $dsn = "mysql:host=" . $host . ";port=" . $port . ";dbname=" . $dbname . ";charset=" . $charset;
            }elseif($driver === "sqlite"){
                $path = $parameters["path"] ?? "";
                $dsn = "sqlite:" . $path;
            }else{
                throw new RuntimeException("Unsupported driver: " . $driver);
            }
            if(!is_array($options)){
                $options = [];
            }
            $pdo = new PDO($dsn, $username, $password, $options);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if($this->timeoutSeconds !== null && $this->timeoutSeconds > 0){
                $pdo->exec("SET SESSION wait_timeout = " . (int)$this->timeoutSeconds);
                $pdo->exec("SET SESSION interactive_timeout = " . (int)$this->timeoutSeconds);
            }
            if(!$this->sql || !is_string($this->sql)){
                throw new RuntimeException("Invalid SQL statement");
            }
            $statement = $pdo->prepare($this->sql);
            $statement->execute($params);
            $rows = [];
            if($statement->columnCount() > 0){
                $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            }
            $affected = $statement->rowCount();
            $id = $pdo->lastInsertId();
            $lastInsertId = null;
            if($id !== "0" && $id !== ""){
                $lastInsertId = (int)$id;
            }
            $payload = [
                "result" => [
                    "rows" => $rows,
                    "affected" => $affected,
                    "lastInsertId" => $lastInsertId,
                ],
                "error" => null
            ];
        }catch(Throwable $e){
            $payload = [
                "result" => null,
                "error" => [
                    "message" => $e->getMessage(),
                    "code" => (int)$e->getCode(),
                ]
            ];
        }finally{
            if($pdo !== null){
                $pdo = null;
            }
        }
        $this->setResult(serialize($payload));
    }

    public function onCompletion(): void{
        $executor = AsyncExecutor::getInstance($this->pluginName);
        if($executor === null){
            return;
        }
        $raw = $this->getResult();
        $payload = is_string($raw) ? unserialize($raw, ["allowed_classes" => false]) : null;
        if(!is_array($payload)){
            $executor->complete($this->id, null, ["message" => "Invalid async result payload", "code" => 0]);
            return;
        }
        $executor->complete(
            $this->id,
            isset($payload["result"]) && is_array($payload["result"]) ? $payload["result"] : null,
            isset($payload["error"]) && is_array($payload["error"]) ? $payload["error"] : null
        );
    }
}