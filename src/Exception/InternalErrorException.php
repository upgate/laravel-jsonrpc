<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Exception;

use Upgate\LaravelJsonRpc\Server\ErrorCode;

final class InternalErrorException extends JsonRpcException
{

    protected function getDefaultMessage(): string
    {
        return 'Internal error';
    }

    protected function getDefaultCode(): int
    {
        return ErrorCode::INTERNAL_ERROR;
    }

}