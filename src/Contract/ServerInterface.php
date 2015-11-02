<?php

namespace Upgate\LaravelJsonRpc\Contract;

use Illuminate\Http\JsonResponse;

interface ServerInterface
{

    /**
     * @return RouteRegistryInterface
     */
    public function router();

    /**
     * @param string $exceptionClass
     * @param callable $handler
     * @param bool $first
     * @return $this
     */
    public function onException($exceptionClass, $handler, $first = false);

    /**
     * @param string $payload
     * @return void
     */
    public function setPayload($payload);

    /**
     * @param $middlewareContext
     * @return JsonResponse
     */
    public function run($middlewareContext = null);

}