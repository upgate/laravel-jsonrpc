<?php

namespace Upgate\LaravelJsonRpc\Exception;

final class BadRequestException extends JsonRpcException
{

    protected function getDefaultMessage()
    {
        return 'Invalid Request';
    }

    protected function getDefaultCode()
    {
        return -32600;
    }

}