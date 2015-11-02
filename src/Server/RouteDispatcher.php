<?php

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Contracts\Container\Container;
use ReflectionException;
use ReflectionMethod;
use Upgate\LaravelJsonRpc\Contract\RouteDispatcher as RouteDispatcherContract;
use Upgate\LaravelJsonRpc\Contract\Route;
use Upgate\LaravelJsonRpc\Exception\InternalErrorException;
use Upgate\LaravelJsonRpc\Exception\InvalidParamsException;

final class RouteDispatcher implements RouteDispatcherContract
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @var null|string
     */
    private $controllerNamespace;

    /**
     * @param Container $container
     * @param string|null $controllerNamespace
     */
    public function __construct(Container $container, $controllerNamespace = null)
    {
        $this->container = $container;
        $this->controllerNamespace = $controllerNamespace ? (string)$controllerNamespace : null;
    }

    /**
     * @param Route $route
     * @param RequestParams $requestParams
     * @return mixed
     */
    public function dispatch(Route $route, RequestParams $requestParams = null)
    {
        $controllerClass = $route->getControllerClass();
        if ($this->controllerNamespace && substr($controllerClass, 0, 1) !== '\\') {
            $controllerClass = $this->controllerNamespace . '\\' . $controllerClass;
        }
        $controller = $this->container->make($controllerClass);
        try {
            $method = new ReflectionMethod($controller, $route->getActionName());
        } catch (ReflectionException $e) {
            throw new InternalErrorException("Method not implemented", $e->getCode(), $e);
        }

        return $this->executeMethod($controller, $method, $requestParams);
    }

    /**
     * @param object $controller
     * @param ReflectionMethod $method
     * @param RequestParams $requestParams
     * @return mixed
     */
    private function executeMethod($controller, ReflectionMethod $method, RequestParams $requestParams = null)
    {
        $params = null;
        $areParamsNamed = null;

        if ($requestParams) {
            $params = $requestParams->getParams() ?: null;
            if ($params) {
                $areParamsNamed = $requestParams->areParamsNamed();
            }
        }

        $args = [];
        foreach ($method->getParameters() as $parameter) {
            $class = $parameter->getClass();
            if ($class) {
                try {
                    $args[] = $this->container->make($class->name);
                } catch (ReflectionException $e) {
                    throw new InternalErrorException($e->getMessage(), 0, $e);
                }
            } else {
                if (null !== $params) {
                    if ($areParamsNamed) {
                        $name = $parameter->getName();
                        if (isset($params[$name])) {
                            $args[] = $params[$name];
                            unset($params[$name]);
                            continue;
                        }
                    } else {
                        if (count($params)) {
                            $args[] = array_shift($params);
                            continue;
                        }
                    }
                }
                try {
                    $args[] = $parameter->getDefaultValue();
                } catch (ReflectionException $e) {
                    throw new InvalidParamsException("\"{$parameter->getName()}\" is required", 0, $e);
                }
            }
        }

        return $method->invokeArgs($controller, $args);
    }

}