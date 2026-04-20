<?php

namespace Mellooh\Himari\async;

interface Promise{

    public function then(callable $onSuccess, ?callable $onError = null): void;

}