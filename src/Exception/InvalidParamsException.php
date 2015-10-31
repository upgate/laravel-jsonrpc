<?php

namespace Upgate\LaravelJsonRpc\Exception;

final class InvalidParamsException extends JsonRpcException
{

    protected function getDefaultMessage()
    {
        return 'Invalid params';
    }

    protected function getDefaultCode()
    {
        return -32602;
    }

}