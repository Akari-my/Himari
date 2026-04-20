<?php

namespace Mellooh\Himari\log;

interface Logger {

    public function log(string $level, string $message, array $context = []): void;
}