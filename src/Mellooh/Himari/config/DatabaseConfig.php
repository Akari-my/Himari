<?php

namespace Mellooh\Himari\config;

class DatabaseConfig {

    public function __construct(
        public readonly string $driver,
        public readonly array $parameters,
        public readonly ?string $username = null,
        public readonly ?string $password = null,
        public readonly array $options = []) {
    }
}