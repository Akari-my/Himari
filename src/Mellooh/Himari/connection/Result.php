<?php

namespace Mellooh\Himari\connection;

class Result{

    public function __construct(public readonly array $rows, public readonly int $affectedRows, public readonly ?int $lastInsertId) {}
}