<?php

namespace Upgate\LaravelJsonRpc\Contract;

use Upgate\LaravelJsonRpc\Server\RequestResponse;

interface RequestExecutor
{

    /**
     * @param Request $request
     * @return RequestResponse
     */
    public function execute(Request $request);

}