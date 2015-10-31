<?php

namespace Upgate\LaravelJsonRpc\Exception;

class ServerErrorException extends JsonRpcException
{

    protected function getDefaultMessage()
    {
        return 'Internal error';
    }

    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        $code = $code ?: $this->getDefaultCode();
        if ($code > -32000 || $code < -32099) {
            throw new \InvalidArgumentException("Code out of range");
        }
        parent::__construct($message, $code, $previous);
    }

    protected function getDefaultCode()
    {
        return -32000;
    }

}