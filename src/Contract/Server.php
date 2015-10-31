<?php

namespace Upgate\LaravelJsonRpc\Contract;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;

interface Server
{

    /**
     * @param string $payload
     * @return void
     */
    public function setPayload($payload);

    /**
     * @param HttpRequest $httpRequest
     * @return JsonResponse
     */
    public function run(HttpRequest $httpRequest);

}