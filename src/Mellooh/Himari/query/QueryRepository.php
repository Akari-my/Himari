<?php

namespace Mellooh\Himari\query;

use Mellooh\Himari\exception\QueryNotFoundException;

class QueryRepository {

    private array $queries = [];

    public function add(QueryDefinition $definition): void{
        if(isset($this->queries[$definition->name])){
            trigger_error(
                "Query '{$definition->name}' is being overwritten. " .
                "Original: {$this->queries[$definition->name]} -> New: {$definition->sql}",
                E_USER_WARNING
            );
        }
        $this->queries[$definition->name] = $definition->sql;
    }

    public function get(string $name): string{
        if (!isset($this->queries[$name])) {
            throw new QueryNotFoundException('Query not found: ' . $name);
        }

        return $this->queries[$name];
    }
}