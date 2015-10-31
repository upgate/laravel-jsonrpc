<?php

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Contracts\Container\Container;
use Illuminate\Pipeline\Pipeline;
use ReflectionException;
use ReflectionMethod;
use Upgate\LaravelJsonRpc\Contract\Request;
use Upgate\LaravelJsonRpc\Contract\RequestDispatcher as RequestDispatcherContract;
use Upgate\LaravelJsonRpc\Contract\Route;
use Illuminate\Http\Request as HttpRequest;
use Upgate\LaravelJsonRpc\Exception\InternalErrorException;
use Upgate\LaravelJsonRpc\Exception\InvalidParamsException;

final class RequestDispatcher implements RequestDispatcherContract
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Route $route
     * @param Request $request
     * @param HttpRequest $httpRequest
     * @return mixed
     */
    public function dispatch(Route $route, Request $request, HttpRequest $httpRequest)
    {
        $pipeline = new Pipeline($this->container);

        return $pipeline->send($httpRequest)->through($route->getMiddlewaresCollection())->then(
            function () use ($route, $request) {
                return $this->dispatchRequest($route, $request);
            }
        );
    }

    /**
     * @param Route $route
     * @param Request $request
     * @return mixed
     */
    private function dispatchRequest(Route $route, Request $request)
    {
        $controller = $this->container->make($route->getControllerClass());
        try {
            $method = new ReflectionMethod($controller, $request->getMethod());
        } catch (ReflectionException $e) {
            throw new InternalErrorException("Method not implemented");
        }

        return $this->executeMethod($controller, $method, $request);
    }

    /**
     * @param object $controller
     * @param ReflectionMethod $method
     * @param Request $request
     * @return mixed
     */
    private function executeMethod($controller, ReflectionMethod $method, Request $request)
    {
        if ($request->getParams()) {
            $areParamsNamed = $request->getParams()->areParamsNamed();
            $requestParams = $request->getParams()->getParams();
        } else {
            $areParamsNamed = null;
            $requestParams = null;
        }

        $args = [];
        foreach ($method->getParameters() as $parameter) {
            $className = $parameter->getClass();
            if ($className) {
                try {
                    $args[] = $this->container->make($className);
                } catch (ReflectionException $e) {
                    throw new InternalErrorException($e->getMessage());
                }
            } else {
                if (null !== $requestParams) {
                    if ($areParamsNamed) {
                        $name = $parameter->getName();
                        if (isset($requestParams[$name])) {
                            $args[] = $requestParams[$name];
                            unset($requestParams[$name]);
                            continue;
                        }
                    } else {
                        if (count($requestParams)) {
                            $args[] = array_shift($requestParams);
                            continue;
                        }
                    }
                }
                try {
                    $args[] = $parameter->getDefaultValue();
                } catch (ReflectionException $e) {
                    throw new InvalidParamsException();
                }
            }
        }

        return $method->invokeArgs($controller, $args);
    }

}