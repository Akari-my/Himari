<?php

namespace Mellooh\Himari\connection;

interface Connection{

    public function execute(string $sql, array $params = []): Result;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollBack(): void;
}