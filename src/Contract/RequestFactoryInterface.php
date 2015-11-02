<?php

namespace Upgate\LaravelJsonRpc\Contract;

interface RequestFactoryInterface
{

    /**
     * @param string $payloadJson
     * @return ExecutableInterface
     */
    public function createFromPayload($payloadJson);

    /**
     * @param object $requestData
     * @return RequestInterface
     */
    public function createRequest($requestData);

}