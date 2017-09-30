<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Server;

final class ErrorCode
{

    const PARSE_ERROR = -32700;
    const INVALID_REQUEST = -32600;
    const METHOD_NOT_FOUND = -32601;
    const INVALID_PARAMS = -32602;
    const INTERNAL_ERROR = -32603;

    private function __construct()
    {
        // pass
    }

}