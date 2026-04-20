<?php

namespace Mellooh\Himari\query;

use Mellooh\Himari\exception\HimariException;
use RuntimeException;

class QueryFileLoader{

    public static function loadFromFiles(string $dialect, array $paths): QueryRepository{
        $repository = new QueryRepository();
        foreach ($paths as $path) {
            self::loadFromFile($dialect, $path, $repository);
        }
        return $repository;
    }

    private static function loadFromFile(string $dialect, string $path, QueryRepository $repository): void{
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new HimariException('Unable to read SQL file: ' . $path);
        }

        $lines = preg_split('/\R/', $contents);
        if ($lines === false) {
            return;
        }

        $currentName = null;
        $currentDialect = null;
        $sqlLines = [];
        $dialect = strtolower($dialect);

        foreach ($lines as $line) {
            $trimmed = ltrim($line);
            if (str_starts_with($trimmed, '-- name:')) {
                if ($currentName !== null) {
                    self::storeStatement($dialect, $currentName, $currentDialect, $sqlLines, $repository);
                }
                $currentName = trim(substr($trimmed, strlen('-- name:')));
                $currentDialect = null;
                $sqlLines = [];
                continue;
            }

            if ($currentName !== null && str_starts_with($trimmed, '-- dialect:')) {
                $currentDialect = strtolower(trim(substr($trimmed, strlen('-- dialect:'))));
                continue;
            }

            if ($currentName !== null) {
                $sqlLines[] = $line;
            }
        }

        if ($currentName !== null) {
            self::storeStatement($dialect, $currentName, $currentDialect, $sqlLines, $repository);
        }
    }

    private static function storeStatement(string $targetDialect, string $name, ?string $statementDialect, array $sqlLines, QueryRepository $repository): void{
        if ($statementDialect !== null && $statementDialect !== $targetDialect) {
            return;
        }
        $sql = trim(implode("\n", $sqlLines));
        if ($sql === '') {
            return;
        }
        $repository->add(new QueryDefinition($name, $sql));
    }
}