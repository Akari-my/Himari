<?php

namespace Mellooh\Himari\async;

interface Executor {

    public function submit(callable $task, ?float $timeoutSeconds = null): Promise;

    public function waitAll(): void;

    public function shutdown(): void;
}