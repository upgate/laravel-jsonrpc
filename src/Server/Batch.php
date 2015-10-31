<?php

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Contracts\Support\Jsonable;
use Upgate\LaravelJsonRpc\Contract\Executable;
use Upgate\LaravelJsonRpc\Contract\RequestExecutor;
use Upgate\LaravelJsonRpc\Contract\RequestFactory;

class Batch implements Executable
{

    private $batch;
    private $requestFactory;

    /**
     * @param array $batch
     * @param RequestFactory $requestFactory
     */
    public function __construct(array $batch, RequestFactory $requestFactory)
    {
        $this->batch = $batch;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @param RequestExecutor $executor
     * @return Jsonable|null
     */
    public function executeWith(RequestExecutor $executor)
    {
        /** @var Request[] $requests */
        $requests = array_map(function($requestData) {
            return $this->requestFactory->createRequest($requestData);
        }, $this->batch);

        $response = new BatchResponse();

        foreach ($requests as $request) {
            $requestResponse = $executor->execute($request);
            if (null !== $requestResponse) {
                $response->add($requestResponse);
            }
        }

        return $response;
    }
}