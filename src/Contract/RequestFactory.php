<?php

namespace Upgate\LaravelJsonRpc\Contract;

interface RequestFactory
{

    /**
     * @param string $payloadJson
     * @return Executable
     */
    public function createFromPayload($payloadJson);

    /**
     * @param object $requestData
     * @return Request
     */
    public function createRequest($requestData);

}