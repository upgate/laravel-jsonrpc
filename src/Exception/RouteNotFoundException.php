<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Exception;

use Upgate\LaravelJsonRpc\Server\ErrorCode;

final class RouteNotFoundException extends JsonRpcException
{

    protected function getDefaultMessage(): string
    {
        return "Method not found";
    }

    protected function getDefaultCode(): int
    {
        return ErrorCode::METHOD_NOT_FOUND;
    }
}