<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Contract;

use Upgate\LaravelJsonRpc\Server\RequestResponse;

interface RequestExecutorInterface
{

    /**
     * @param RequestInterface $request
     * @return RequestResponse
     */
    public function execute(RequestInterface $request);

}