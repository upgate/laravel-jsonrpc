<?php

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Http\JsonResponse;
use Psr\Log\LoggerInterface;
use Upgate\LaravelJsonRpc\Contract\Request;
use Upgate\LaravelJsonRpc\Contract\RouteDispatcher;
use Upgate\LaravelJsonRpc\Contract\MiddlewareDispatcher;
use Upgate\LaravelJsonRpc\Contract\RequestExecutor;
use Upgate\LaravelJsonRpc\Contract\RequestFactory;
use Upgate\LaravelJsonRpc\Contract\RouteRegistry;
use Upgate\LaravelJsonRpc\Contract\Server as ServerContract;
use Upgate\LaravelJsonRpc\Exception\InternalErrorException;
use Upgate\LaravelJsonRpc\Exception\JsonRpcException;

class Server implements ServerContract, RequestExecutor
{

    /**
     * @var string|null
     */
    private $payload = null;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var RouteRegistry
     */
    private $router;

    /**
     * @var RouteDispatcher
     */
    private $routeDispatcher;

    /**
     * @var MiddlewareDispatcher
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
        RequestFactory $requestFactory,
        RouteRegistry $router,
        RouteDispatcher $routeDispatcher,
        MiddlewareDispatcher $middlewareDispatcher,
        LoggerInterface $logger
    ) {
        $this->requestFactory = $requestFactory;
        $this->router = $router;
        $this->routeDispatcher = $routeDispatcher;
        $this->middlewareDispatcher = $middlewareDispatcher;
        $this->logger = $logger;
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
     * @param Request $request
     * @return RequestResponse|null
     */
    public function execute(Request $request)
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

            return $request->getId() ? RequestResponse::constructErrorResponse($request->getId(), $e) : null;
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

            return RequestResponse::constructErrorResponse($request->getId(), new InternalErrorException());
        }
    }

    /**
     * @param \Exception $e
     * @param Request $request
     * @return bool|RequestResponse
     */
    private function handleException(\Exception $e, Request $request)
    {
        foreach ($this->exceptionHandlers as $className => $handler) {
            if ($e instanceof $className) {
                return $handler($e, $request);
            }
        }

        return false;
    }

}