<?php

namespace Mellooh\Himari\transaction;

class TransactionReference{

    public function __construct(public readonly int $stepIndex, public readonly string $type, readonly ?int $rowIndex = null, public readonly ?string $column = null) {}

    public static function lastInsertId(int $stepIndex): self{
        return new self($stepIndex, 'lastInsertId');
    }

    public static function row(int $stepIndex, int $rowIndex, string $column): self{
        return new self($stepIndex, 'row', $rowIndex, $column);
    }

}