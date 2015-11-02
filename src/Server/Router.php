<?php

namespace Upgate\LaravelJsonRpc\Server;

use Upgate\LaravelJsonRpc\Contract\Route as RouteContract;
use Upgate\LaravelJsonRpc\Contract\RouteRegistry;
use Upgate\LaravelJsonRpc\Exception\RouteNotFoundException;

final class Router implements RouteRegistry
{

    /**
     * @var MethodBinding[]
     */
    private $methodBindings = [];

    /**
     * @var ControllerBinding[]
     */
    private $controllerBindings = [];

    private $middlewaresCollection;

    public function __construct(MiddlewaresCollection $middlewaresCollection = null)
    {
        $this->middlewaresCollection = $middlewaresCollection ?: new MiddlewaresCollection();
    }

    /**
     * @param string $method
     * @param string $binding
     */
    public function bind($method, $binding)
    {
        $this->methodBindings[strtolower($method)] = new MethodBinding($binding, $this->middlewaresCollection);
    }

    /**
     * @param string $namespace
     * @param string $controller
     */
    public function bindController($namespace, $controller)
    {
        $this->controllerBindings[strtolower($namespace)] = new ControllerBinding(
            $controller,
            $this->middlewaresCollection
        );
    }

    /**
     * @param callable|null $middlewaresConfigurator
     * @param callable $routesConfigurator
     */
    public function group(callable $middlewaresConfigurator = null, callable $routesConfigurator)
    {
        $middlewaresSubcollection = $this->middlewaresCollection ? clone $this->middlewaresCollection : null;
        if ($middlewaresConfigurator) {
            $middlewaresSubcollection = $middlewaresConfigurator($middlewaresSubcollection)
                ?: $middlewaresSubcollection;
        }
        $subrouter = new self($middlewaresSubcollection);
        $this->mergeBindingsFrom($routesConfigurator($subrouter) ?: $subrouter);
    }

    protected function mergeBindingsFrom(Router $router)
    {
        $this->methodBindings = $router->methodBindings + $this->methodBindings;
        $this->controllerBindings = $router->controllerBindings + $this->controllerBindings;
    }

    /**
     * @param string $method
     * @return RouteContract
     */
    public function resolve($method)
    {
        return $this->findBinding($method)->resolveRoute($method);
    }

    /**
     * @param $method
     * @return Binding
     */
    private function findBinding($method)
    {
        $method = strtolower($method);
        if (isset($this->methodBindings[$method])) {
            return $this->methodBindings[$method];
        }

        $namespace = strtok($method, '.');
        if (isset($this->controllerBindings[$namespace])) {
            return $this->controllerBindings[$namespace];
        }

        throw new RouteNotFoundException($method);
    }

}