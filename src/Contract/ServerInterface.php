<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Contract;

use Illuminate\Http\JsonResponse;

interface ServerInterface
{

    /**
     * @return RouteRegistryInterface
     */
    public function router(): RouteRegistryInterface;

    /**
     * @param string $exceptionClass
     * @param callable $handler
     * @param bool $first
     * @return $this
     */
    public function onException(string $exceptionClass, callable $handler, bool $first = false): ServerInterface;

    /**
     * @param array $aliases
     * @return $this
     */
    public function registerMiddlewareAliases(array $aliases): ServerInterface;

    /**
     * @param string $payload
     * @return void
     */
    public function setPayload(string $payload): void;

    /**
     * @param string|null $controllerNamespace
     * @return $this
     */
    public function setControllerNamespace(string $controllerNamespace = null): ServerInterface;

    /**
     * @param mixed $middlewareContext
     * @return JsonResponse
     */
    public function run($middlewareContext = null): JsonResponse;

}