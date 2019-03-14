<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Contract;

interface RouteRegistryInterface
{

    /**
     * @param string $middleware
     * @return $this
     */
    public function addMiddleware(string $middleware): RouteRegistryInterface;

    /**
     * @param array $middlewares
     * @return $this
     */
    public function addMiddlewares(array $middlewares): RouteRegistryInterface;

    /**
     * @param MiddlewareAliasRegistryInterface|null $aliases
     * @return $this
     */
    public function setMiddlewareAliases(MiddlewareAliasRegistryInterface $aliases = null): RouteRegistryInterface;

    /**
     * @param string $method
     * @param string $binding
     * @return $this
     */
    public function bind(string $method, string $binding): RouteRegistryInterface;

    /**
     * @param string $namespace
     * @param string $controller
     * @return $this
     */
    public function bindController(string $namespace, string $controller): RouteRegistryInterface;

    /**
     * @param callable|array|string|null $middlewaresConfigurator
     * @param callable $routesConfigurator
     * @return $this
     */
    public function group($middlewaresConfigurator, callable $routesConfigurator): RouteRegistryInterface;

    /**
     * @param string $method
     * @return RouteInterface
     */
    public function resolve(string $method): RouteInterface;

}