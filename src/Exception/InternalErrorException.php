<?php

namespace Upgate\LaravelJsonRpc\Exception;

final class InternalErrorException extends JsonRpcException
{

    protected function getDefaultMessage()
    {
        return 'Internal error';
    }

    protected function getDefaultCode()
    {
        return -32603;
    }

}