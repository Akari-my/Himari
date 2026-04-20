![minecraft_title (1](https://github.com/user-attachments/assets/9c728b63-2215-4192-8d0d-97b9633b291a)

<p align="center">
  <a href="https://php.net">
    <img src="https://img.shields.io/badge/PHP-8.1+-blue.svg" alt="PHP 8.1+">
  </a>
  <a href="https://pocketmine.net">
    <img src="https://img.shields.io/badge/PocketMine-5.0+-blue.svg" alt="PocketMine 5.0+">
  </a>
</p>

<p align="center">
  <b>Asynchronous SQL library for PocketMine</b><br>
  Thread-safe database operations with promise-based API
</p>

---

## What is Himari?

Himari is a PocketMine virion that provides **asynchronous SQL operations** for your plugins. It allows you to execute database queries without blocking the main server thread, preventing lag spikes during database operations.

### Features

- **Non-blocking async queries** - Execute SQL without freezing the server
- **Promise-based API** - Clean, chainable callback system
- **Multiple database support** - MySQL and SQLite
- **Query file loading** - SQL files with multiple named queries
- **Built-in error handling** - Automatic logging of unhandled errors
- **Transaction support** - Plan and execute multi-step transactions
- **Pool-based execution** - Leverages PocketMine's async pool

---

## Quick Start

### 1. Create DatabaseConfig

```php
use Mellooh\Himari\config\DatabaseConfig;

// For MySQL
$config = new DatabaseConfig(
    driver: "mysql",
    parameters: [
        "host" => "127.0.0.1",
        "port" => 3306,
        "database" => "minecraft",
        "charset" => "utf8mb4"
    ],
    username: "root",
    password: "secret"
);

// For SQLite
$config = new DatabaseConfig(
    driver: "sqlite",
    parameters: [
        "path" => "plugin_data/mydb.sqlite"
    ]
);
```

### 2. Create Connector

```php
use Mellooh\Himari\Himari;

$connector = Himari::createConnector(
    $this,              // Plugin instance
    $config,           // DatabaseConfig
    ["queries.sql"]    // SQL files path
);
```

### 3. Execute Queries

```php
// SELECT - returns array of rows
$connector->select("getPlayers", [], function(array $rows) {
    foreach ($rows as $row) {
        $this->getLogger()->info("Player: " . $row["name"]);
    }
});

// INSERT - returns lastInsertId
$connector->insert("createPlayer", [
    "name" => "Steve",
    "xp" => 100
], function(?int $insertId) {
    $this->getLogger()->info("Created player ID: " . $insertId);
});

// UPDATE/DELETE - returns affected rows
$connector->change("updateXp", [
    "xp" => 200,
    "name" => "Steve"
], function(int $affected) {
    $this->getLogger()->info("Updated $affected rows");
});
```

---

## Query Files

Create SQL files with named queries using comments:

```sql
-- name: getPlayers
-- dialect: mysql
SELECT * FROM players WHERE active = 1;

-- name: createPlayer
INSERT INTO players (name, xp) VALUES (:name, :xp);

-- name: updateXp
UPDATE players SET xp = :xp WHERE name = :name;

-- name: deletePlayer
DELETE FROM players WHERE name = :name;
```

### Dialect Support

You can specify database-specific queries:

```sql
-- name: getPlayerCount
-- dialect: mysql
SELECT COUNT(*) as count FROM players;

-- name: getPlayerCount
-- dialect: sqlite
SELECT COUNT(*) as count FROM players;
```

The loader automatically selects the right query based on your driver.

---

## API Reference

### Connector Methods

| Method | Description | Returns |
|--------|-------------|---------|
| `select($name, $params, $onSuccess, $onError, $timeout)` | Execute SELECT query | `array` |
| `execute($name, $params, $onSuccess, $onError, $timeout)` | Execute any query | `Result` |
| `insert($name, $params, $onSuccess, $onError, $timeout)` | Execute INSERT | `?int` |
| `change($name, $params, $onSuccess, $onError, $timeout)` | Execute UPDATE/DELETE | `int` |
| `waitAll()` | Wait for all pending queries | `void` |
| `shutdown()` | Shutdown executor | `void` |

### DatabaseConfig

```php
new DatabaseConfig(
    string $driver,        // "mysql" or "sqlite"
    array $parameters,    // driver-specific config
    ?string $username,    // MySQL username
    ?string $password,    // MySQL password
    array $options        // PDO options
);
```

---

## Error Handling

### With Error Callback

```php
$connector->select("getPlayers", [], function(array $rows) {
    // Success
}, function(\Throwable $e) {
    $this->getLogger()->error("Query failed: " . $e->getMessage());
});
```

### Without Error Callback (Auto-logged)

If you don't provide an error callback, Himari will automatically log errors using the logger:

```php
// Pass null as error callback - errors logged automatically
$connector->select("getPlayers", [], function(array $rows) {
    // ...
}, null);
```

---

## Full Example

```php
<?php

declare(strict_types=1);

namespace MyPlugin;

use Mellooh\Himari\config\DatabaseConfig;
use Mellooh\Himari\Himari;
use pocketmine\plugin\PluginBase;

class MyPlugin extends PluginBase {

    private ?Connector $connector = null;

    public function onEnable(): void {
        $config = new DatabaseConfig(
            driver: "mysql",
            parameters: [
                "host" => "127.0.0.1",
                "port" => 3306,
                "database" => "minecraft",
                "charset" => "utf8mb4"
            ],
            username: "root",
            password: "secret"
        );

        $this->connector = Himari::createConnector(
            $this,
            $config,
            ["queries.sql"]
        );

        $this->initDatabase();
    }

    private function initDatabase(): void {
        $this->connector->execute("createTable", [], function(Result $result) {
            $this->getLogger()->info("Database initialized!");
        }, function(\Throwable $e) {
            $this->getLogger()->error("Init failed: " . $e->getMessage());
        });
    }

    public function onDisable(): void {
        if ($this->connector !== null) {
            $this->connector->waitAll();
            $this->connector->shutdown();
        }
    }
}
```

---

## Requirements

- PHP 8.1+
- PocketMine-MP 5.0+
- PDO extension (MySQL or SQLite)

---

## License

MIT License

---

<p align="center">
  Made with ❤️ by Mellooh_
</p>
