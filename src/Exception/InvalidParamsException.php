<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Exception;

use Upgate\LaravelJsonRpc\Server\ErrorCode;

final class InvalidParamsException extends JsonRpcException
{

    protected function getDefaultMessage(): string
    {
        return 'Invalid params';
    }

    protected function getDefaultCode(): int
    {
        return ErrorCode::INVALID_PARAMS;
    }

}