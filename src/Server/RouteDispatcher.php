<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Contracts\Container\Container;
use ReflectionException;
use ReflectionMethod;
use Upgate\LaravelJsonRpc\Contract\RouteDispatcherInterface;
use Upgate\LaravelJsonRpc\Contract\RouteInterface;
use Upgate\LaravelJsonRpc\Exception\InternalErrorException;
use Upgate\LaravelJsonRpc\Exception\InvalidParamsException;

final class RouteDispatcher implements RouteDispatcherInterface
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
    public function __construct(Container $container, string $controllerNamespace = null)
    {
        $this->container = $container;
        $this->setControllerNamespace($controllerNamespace);
    }

    /**
     * @param string|null $controllerNamespace
     * @return RouteDispatcherInterface
     */
    public function setControllerNamespace(string $controllerNamespace = null): RouteDispatcherInterface
    {
        $this->controllerNamespace = $controllerNamespace;

        return $this;
    }

    /**
     * @param RouteInterface $route
     * @param RequestParams $requestParams
     * @return mixed
     */
    public function dispatch(RouteInterface $route, RequestParams $requestParams = null)
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
                    if ($parameter->isVariadic()) {
                        foreach ($params as $key => $value) {
                            $args[] = $this->cast($value, $parameter);
                        }
                        break;
                    }

                    if ($areParamsNamed) {
                        $name = $parameter->getName();
                        if (array_key_exists($name, $params)) {
                            $args[] = $this->cast($params[$name], $parameter);
                            unset($params[$name]);
                            continue;
                        }
                    } else {
                        if (count($params)) {
                            $args[] = $this->cast(array_shift($params), $parameter);
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

    private function cast($value, \ReflectionParameter $parameter)
    {
        $type = $parameter->getType();
        if ($type && $type->isBuiltin()) {
            if ($value === null && $type->allowsNull()) {
                return null;
            }
            $parameterType = (string)$type;
            $valueType = gettype($type);
            if ($valueType === $parameterType) {
                return $value;
            }
            try {
                settype($value, $parameterType);
            } catch (\Exception $e) {
                throw new InvalidParamsException(
                    "\"{$parameter->getName()}\" type mismatch: cannot cast $valueType to $parameterType", 0, $e
                );
            }
        }

        return $value;
    }

}