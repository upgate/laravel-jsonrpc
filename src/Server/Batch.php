<?php

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Upgate\LaravelJsonRpc\Contract\ExecutableInterface;
use Upgate\LaravelJsonRpc\Contract\RequestExecutorInterface;
use Upgate\LaravelJsonRpc\Contract\RequestFactoryInterface;

final class Batch implements ExecutableInterface, Arrayable
{

    private $batch;
    private $requestFactory;

    /**
     * @param array $batch
     * @param RequestFactoryInterface $requestFactory
     */
    public function __construct(array $batch, RequestFactoryInterface $requestFactory)
    {
        $this->batch = $batch;
        $this->requestFactory = $requestFactory;
    }

    /**
     * @param RequestExecutorInterface $executor
     * @return Jsonable|null
     */
    public function executeWith(RequestExecutorInterface $executor)
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

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->batch;
    }

}