<?php

namespace Mellooh\Himari\async;

use Throwable;

class SyncExecutor implements Executor{

    public function submit(callable $task, ?float $timeoutSeconds = null): Promise{
        $deferred = new Deferred();

        if(!$task instanceof SqlTask){
            $deferred->reject(new \RuntimeException('Task must be an SqlTask instance'));
            return $deferred;
        }

        $deferred->reject(new \RuntimeException('SyncExecutor requires a Connection to be set. Use AsyncExecutor for PocketMine async execution.'));
        return $deferred;
    }

    public function waitAll(): void{}

    public function shutdown(): void{}
}