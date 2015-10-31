<?php

namespace Upgate\LaravelJsonRpc\Contract;

use Illuminate\Contracts\Support\Jsonable;

interface Executable
{

    /**
     * @param RequestExecutor $executor
     * @return Jsonable|null
     */
    public function executeWith(RequestExecutor $executor);

}