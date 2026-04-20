<?php

namespace Mellooh\Himari\connection;

use Mellooh\Himari\exception\ConnectionException;
use PDO;
use PDOException;

class PdoConnection implements Connection{

    private PDO $pdo;

    public function __construct(PDO $pdo){
        $this->pdo = $pdo;
    }

    public function execute(string $sql, array $params = []): Result{
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($params);
            $rows = [];
            if ($statement->columnCount() > 0) {
                $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
            }
            $affected = $statement->rowCount();
            $lastInsertId = null;
            $id = $this->pdo->lastInsertId();
            if ($id !== '0' && $id !== '') {
                $lastInsertId = (int) $id;
            }
            return new Result($rows, $affected, $lastInsertId);
        } catch (PDOException $e) {
            throw new ConnectionException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function beginTransaction(): void{
        try {
            $this->pdo->beginTransaction();
        } catch (PDOException $e) {
            throw new ConnectionException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function commit(): void{
        try {
            $this->pdo->commit();
        } catch (PDOException $e) {
            throw new ConnectionException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function rollBack(): void{
        try {
            $this->pdo->rollBack();
        } catch (PDOException $e) {
            throw new ConnectionException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}