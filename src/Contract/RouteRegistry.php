<?php

namespace Upgate\LaravelJsonRpc\Contract;

interface RouteRegistry
{

    /**
     * @param string $method
     * @param string $binding
     */
    public function bind($method, $binding);

    /**
     * @param string $namespace
     * @param string $controller
     */
    public function bindController($namespace, $controller);

    /**
     * @param callable $middlewaresConfigurator
     * @param callable $routesConfigurator
     */
    public function group(callable $middlewaresConfigurator, callable $routesConfigurator);

    /**
     * @param string $method
     * @return Route
     */
    public function resolve($method);

}