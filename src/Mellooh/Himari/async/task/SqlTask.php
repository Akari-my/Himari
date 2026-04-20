<?php

namespace Mellooh\Himari\async\task;

class SqlTask {

    public string $sql;
    public array $params;

    public function __construct(string $sql, array $params){
        $this->sql = $sql;
        $this->params = $params;
    }

    public function __invoke(): void{
    }

}