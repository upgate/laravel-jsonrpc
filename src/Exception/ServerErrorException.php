<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Exception;

use Upgate\LaravelJsonRpc\Server\ErrorCode;

class ServerErrorException extends JsonRpcException
{

    public function __construct(string $message = "", int $code = 0, \Exception $previous = null)
    {
        $code = $code ?: $this->getDefaultCode();
        parent::__construct($message, $code, $previous);
    }

    protected function getDefaultMessage(): string
    {
        return 'Internal error';
    }

    protected function getDefaultCode(): int
    {
        return ErrorCode::INTERNAL_ERROR;
    }

}