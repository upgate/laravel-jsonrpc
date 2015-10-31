<?php

namespace Upgate\LaravelJsonRpc\Exception;

final class RouteNotFoundException extends JsonRpcException
{

    protected function getDefaultMessage()
    {
        return "Method not found";
    }

    protected function getDefaultCode()
    {
        return -32601;
    }
}