<?php

namespace Mellooh\Himari\async;

class Deferred implements Promise{

    private bool $resolved = false;
    private bool $rejected = false;
    private mixed $value = null;
    private ?\Throwable $reason = null;
    private array $successHandlers = [];
    private array $errorHandlers = [];

    public function then(callable $onSuccess, ?callable $onError = null): void{
        if ($this->resolved) {
            $onSuccess($this->value);
            return;
        }
        if ($this->rejected) {
            if ($onError !== null) {
                $onError($this->reason);
            }
            return;
        }
        $this->successHandlers[] = $onSuccess;
        if ($onError !== null) {
            $this->errorHandlers[] = $onError;
        }
    }

    public function resolve(mixed $value): void{
        if ($this->resolved || $this->rejected) {
            return;
        }
        $this->resolved = true;
        $this->value = $value;
        foreach ($this->successHandlers as $handler) {
            $handler($value);
        }
        $this->successHandlers = [];
        $this->errorHandlers = [];
    }

    public function reject(\Throwable $reason): void{
        if ($this->resolved || $this->rejected) {
            return;
        }
        $this->rejected = true;
        $this->reason = $reason;
        foreach ($this->errorHandlers as $handler) {
            $handler($reason);
        }
        $this->successHandlers = [];
        $this->errorHandlers = [];
    }
}