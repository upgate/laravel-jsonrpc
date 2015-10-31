<?php

namespace Upgate\LaravelJsonRpc\Exception;

final class ParseErrorException extends JsonRpcException
{

    protected function getDefaultMessage()
    {
        return 'Parse error';
    }

    protected function getDefaultCode()
    {
        return -32700;
    }

}