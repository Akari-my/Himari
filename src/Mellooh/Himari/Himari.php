<?php

namespace Mellooh\Himari;

use Mellooh\Himari\async\AsyncExecutor;
use Mellooh\Himari\config\DatabaseConfig;
use Mellooh\Himari\core\Connector;
use Mellooh\Himari\exception\ConnectionException;
use Mellooh\Himari\log\Logger;
use Mellooh\Himari\query\QueryFileLoader;
use pocketmine\plugin\PluginBase;

class Himari {

    private static function createConnectorInternal(DatabaseConfig $config, array $sqlPaths, AsyncExecutor $executor, ?Logger $logger = null): Connector{
        $driver = strtolower($config->driver);
        if($driver !== 'mysql' && $driver !== 'sqlite'){
            throw new ConnectionException('Unsupported driver: ' . $config->driver);
        }
        $queries = QueryFileLoader::loadFromFiles($driver, $sqlPaths);
        return new Connector($queries, $executor, $logger);
    }

    public static function createConnector(PluginBase $plugin, DatabaseConfig $config, array $sqlPaths, ?Logger $logger = null): Connector{
        $executor = new AsyncExecutor($plugin, $config);
        return self::createConnectorInternal($config, $sqlPaths, $executor, $logger);
    }
}