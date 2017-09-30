<?php
declare(strict_types=1);

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
     * @return RouteRegistryInterface
     */
    public function bind(string $method, string $binding): RouteRegistryInterface
    {
        $this->methodBindings[strtolower($method)] = new MethodBinding($binding, $this->middlewaresCollection);

        return $this;
    }

    /**
     * @param string $namespace
     * @param string $controller
     * @return RouteRegistryInterface
     */
    public function bindController(string $namespace, string $controller): RouteRegistryInterface
    {
        $this->controllerBindings[strtolower($namespace)] = new ControllerBinding(
            $controller,
            $this->middlewaresCollection
        );

        return $this;
    }

    /**
     * @param callable|string|array|null $middlewaresConfigurator
     * @param callable $routesConfigurator
     * @return RouteRegistryInterface
     */
    public function group($middlewaresConfigurator = null, callable $routesConfigurator): RouteRegistryInterface
    {
        $middlewaresSubcollection = $this->middlewaresCollection ? clone $this->middlewaresCollection : null;
        if (null !== $middlewaresConfigurator) {
            if (is_callable($middlewaresConfigurator)) {
                $middlewaresSubcollection = $middlewaresConfigurator($middlewaresSubcollection)
                    ?: $middlewaresSubcollection;
            } else {
                foreach ((array)$middlewaresConfigurator as $middleware) {
                    $middlewaresSubcollection->addMiddleware($middleware);
                }
            }
        }
        $subrouter = new self($middlewaresSubcollection);
        $this->mergeBindingsFrom($routesConfigurator($subrouter) ?: $subrouter);

        return $this;
    }

    /**
     * @param string $method
     * @return RouteContract
     */
    public function resolve(string $method): RouteContract
    {
        return $this->findBinding($method)->resolveRoute($method);
    }

    /**
     * @param string $middleware
     * @return RouteRegistryInterface
     */
    public function addMiddleware(string $middleware): RouteRegistryInterface
    {
        $this->middlewaresCollection->addMiddleware($middleware);

        return $this;
    }

    /**
     * @param array $middlewares
     * @return RouteRegistryInterface
     */
    public function addMiddlewares(array $middlewares): RouteRegistryInterface
    {
        foreach ($middlewares as $middleware) {
            $this->addMiddleware($middleware);
        }

        return $this;
    }

    /**
     * @param MiddlewareAliasRegistryInterface|null $aliases
     * @return RouteRegistryInterface
     */
    public function setMiddlewareAliases(MiddlewareAliasRegistryInterface $aliases = null): RouteRegistryInterface
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
    private function findBinding(string $method): Binding
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