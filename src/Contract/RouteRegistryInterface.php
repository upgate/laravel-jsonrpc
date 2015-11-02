<?php

namespace Upgate\LaravelJsonRpc\Contract;

interface RouteRegistryInterface
{

    /**
     * @param array|string $middleware
     * @return $this
     */
    public function addMiddleware($middleware);

    /**
     * @param string $method
     * @param string $binding
     * @return $this
     */
    public function bind($method, $binding);

    /**
     * @param string $namespace
     * @param string $controller
     * @return $this
     */
    public function bindController($namespace, $controller);

    /**
     * @param callable $middlewaresConfigurator
     * @param callable $routesConfigurator
     * @return $this
     */
    public function group(callable $middlewaresConfigurator, callable $routesConfigurator);

    /**
     * @param string $method
     * @return RouteInterface
     */
    public function resolve($method);

}