<?php

namespace Upgate\LaravelJsonRpc\Exception;

abstract class JsonRpcException extends \RuntimeException
{

    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct(
            $message ?: $this->getDefaultMessage(),
            intval($code ?: $this->getDefaultCode()),
            $previous
        );
    }

    abstract protected function getDefaultMessage();

    abstract protected function getDefaultCode();

}