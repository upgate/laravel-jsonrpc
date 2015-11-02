<?php

namespace Upgate\LaravelJsonRpc\Contract;

use Illuminate\Contracts\Support\Jsonable;

interface ExecutableInterface
{

    /**
     * @param RequestExecutorInterface $executor
     * @return Jsonable|null
     */
    public function executeWith(RequestExecutorInterface $executor);

}