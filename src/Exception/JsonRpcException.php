<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Exception;

abstract class JsonRpcException extends \RuntimeException
{

    public function __construct(string $message = "", int $code = 0, \Exception $previous = null)
    {
        parent::__construct(
            $message ?: $this->getDefaultMessage(),
            intval($code ?: $this->getDefaultCode()),
            $previous
        );
    }

    abstract protected function getDefaultMessage(): string;

    abstract protected function getDefaultCode(): int;

}