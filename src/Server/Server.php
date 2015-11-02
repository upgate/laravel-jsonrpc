<?php

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Http\JsonResponse;
use Psr\Log\LoggerInterface;
use Upgate\LaravelJsonRpc\Contract\RequestInterface;
use Upgate\LaravelJsonRpc\Contract\RouteDispatcherInterface;
use Upgate\LaravelJsonRpc\Contract\MiddlewareDispatcherInterface;
use Upgate\LaravelJsonRpc\Contract\RequestExecutorInterface;
use Upgate\LaravelJsonRpc\Contract\RequestFactoryInterface;
use Upgate\LaravelJsonRpc\Contract\RouteRegistryInterface;
use Upgate\LaravelJsonRpc\Contract\ServerInterface as ServerContract;
use Upgate\LaravelJsonRpc\Exception\InternalErrorException;
use Upgate\LaravelJsonRpc\Exception\JsonRpcException;

class Server implements ServerContract, RequestExecutorInterface
{

    /**
     * @var string|null
     */
    private $payload = null;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var RouteRegistryInterface
     */
    private $router;

    /**
     * @var RouteDispatcherInterface
     */
    private $routeDispatcher;

    /**
     * @var MiddlewareDispatcherInterface
     */
    private $middlewareDispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var callable[]
     */
    private $exceptionHandlers = [];

    private $middlewareContext = null;

    public function __construct(
        RequestFactoryInterface $requestFactory,
        RouteRegistryInterface $router,
        RouteDispatcherInterface $routeDispatcher,
        MiddlewareDispatcherInterface $middlewareDispatcher,
        LoggerInterface $logger
    ) {
        $this->requestFactory = $requestFactory;
        $this->router = $router;
        $this->routeDispatcher = $routeDispatcher;
        $this->middlewareDispatcher = $middlewareDispatcher;
        $this->logger = $logger;
    }

    /**
     * @return RouteRegistryInterface
     */
    public function router()
    {
        return $this->router;
    }

    /**
     * @param string $exceptionClass
     * @param callable $handler
     * @param bool $first
     * @return $this
     */
    public function onException($exceptionClass, $handler, $first = false)
    {
        if ($first) {
            $this->exceptionHandlers = [$exceptionClass => $handler] + $this->exceptionHandlers;
        } else {
            $this->exceptionHandlers[$exceptionClass] = $handler;
        }

        return $this;
    }

    /**
     * @param string $payload
     * @return void
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * @param null $middlewareContext
     * @return JsonResponse
     */
    public function run($middlewareContext = null)
    {
        $this->middlewareContext = $middlewareContext;

        if (null === $this->payload) {
            $payload = file_get_contents('php://input');
        } else {
            $payload = $this->payload;
        }

        return new JsonResponse($this->requestFactory->createFromPayload($payload)->executeWith($this));
    }

    /**
     * @param RequestInterface $request
     * @return RequestResponse|null
     */
    public function execute(RequestInterface $request)
    {
        try {
            $route = $this->router->resolve($request->getMethod());

            $result = $this->middlewareDispatcher->dispatch(
                $route->getMiddlewaresCollection(),
                $this->middlewareContext,
                function () use ($route, $request) {
                    return $this->routeDispatcher->dispatch($route, $request->getParams());
                }
            );

            return $request->getId() ? new RequestResponse($request->getId(), $result) : null;
        } catch (JsonRpcException $e) {
            if (!$request->getId()) {
                return null;
            }

            return $request->getId() ? RequestResponse::constructExceptionErrorResponse($request->getId(), $e) : null;
        } catch (\Exception $e) {
            $handlerResult = $this->handleException($e, $request);

            if (!$handlerResult) {
                $this->logger->error($e);
            }

            if (!$request->getId()) {
                return null;
            }

            if ($handlerResult instanceof RequestResponse) {
                return $handlerResult;
            }

            return RequestResponse::constructExceptionErrorResponse($request->getId(), new InternalErrorException());
        }
    }

    /**
     * @param \Exception $e
     * @param RequestInterface $request
     * @return bool|RequestResponse
     */
    private function handleException(\Exception $e, RequestInterface $request)
    {
        foreach ($this->exceptionHandlers as $className => $handler) {
            if ($e instanceof $className) {
                return $handler($e, $request);
            }
        }

        return false;
    }

}