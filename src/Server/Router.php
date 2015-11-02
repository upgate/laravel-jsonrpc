<?php

namespace Upgate\LaravelJsonRpc\Server;

use Upgate\LaravelJsonRpc\Contract\MiddlewareAliasRegistryInterface;
use Upgate\LaravelJsonRpc\Contract\RouteInterface as RouteContract;
use Upgate\LaravelJsonRpc\Contract\RouteRegistryInterface;
use Upgate\LaravelJsonRpc\Exception\RouteNotFoundException;

final class Router implements RouteRegistryInterface
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
     * @return $this
     */
    public function bind($method, $binding)
    {
        $this->methodBindings[strtolower($method)] = new MethodBinding($binding, $this->middlewaresCollection);

        return $this;
    }

    /**
     * @param string $namespace
     * @param string $controller
     * @return $this
     */
    public function bindController($namespace, $controller)
    {
        $this->controllerBindings[strtolower($namespace)] = new ControllerBinding(
            $controller,
            $this->middlewaresCollection
        );

        return $this;
    }

    /**
     * @param callable|null $middlewaresConfigurator
     * @param callable $routesConfigurator
     * @return $this
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

        return $this;
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
     * @param array|string $middleware
     * @return $this
     */
    public function addMiddleware($middleware)
    {
        $middlewares = (array)$middleware;
        foreach ($middlewares as $middleware) {
            $this->middlewaresCollection->addMiddleware($middleware);
        }

        return $this;
    }

    /**
     * @param MiddlewareAliasRegistryInterface|null $aliases
     * @return $this
     */
    public function setMiddlewareAliases(MiddlewareAliasRegistryInterface $aliases = null)
    {
        $this->middlewaresCollection->setMiddlewareAliases($aliases);

        return $this;
    }

    /**
     * @param Router $router
     */
    private function mergeBindingsFrom(Router $router)
    {
        $this->methodBindings = $router->methodBindings + $this->methodBindings;
        $this->controllerBindings = $router->controllerBindings + $this->controllerBindings;
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