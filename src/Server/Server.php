<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Http\JsonResponse;
use Psr\Log\LoggerInterface;
use Upgate\LaravelJsonRpc\Contract\RequestInterface;
use Upgate\LaravelJsonRpc\Contract\RouteDispatcherInterface;
use Upgate\LaravelJsonRpc\Contract\MiddlewareDispatcherInterface;
use Upgate\LaravelJsonRpc\Contract\RequestExecutorInterface;
use Upgate\LaravelJsonRpc\Contract\RequestFactoryInterface;
use Upgate\LaravelJsonRpc\Contract\RouteRegistryInterface;
use Upgate\LaravelJsonRpc\Contract\ServerInterface;
use Upgate\LaravelJsonRpc\Exception\InternalErrorException;
use Upgate\LaravelJsonRpc\Exception\JsonRpcException;

class Server implements ServerInterface, RequestExecutorInterface
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
    public function router(): RouteRegistryInterface
    {
        return $this->router;
    }

    /**
     * @param string $exceptionClass
     * @param callable $handler
     * @param bool $first
     * @return ServerInterface
     */
    public function onException(string $exceptionClass, callable $handler, bool $first = false): ServerInterface
    {
        if ($first) {
            $this->exceptionHandlers = [$exceptionClass => $handler] + $this->exceptionHandlers;
        } else {
            $this->exceptionHandlers[$exceptionClass] = $handler;
        }

        return $this;
    }

    /**
     * @param string|null $controllerNamespace
     * @return ServerInterface
     */
    public function setControllerNamespace(string $controllerNamespace = null): ServerInterface
    {
        $this->routeDispatcher->setControllerNamespace($controllerNamespace);

        return $this;
    }

    /**
     * @param array $aliases
     * @return ServerInterface
     */
    public function registerMiddlewareAliases(array $aliases): ServerInterface
    {
        $this->router->setMiddlewareAliases($aliases);

        return $this;
    }

    /**
     * @param string $payload
     * @return void
     */
    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }

    /**
     * @param mixed $middlewareContext
     * @param string|null $payload
     * @return JsonResponse
     */
    public function run($middlewareContext = null, string $payload = null): JsonResponse
    {
        $this->middlewareContext = $middlewareContext;

        if (null === $payload) {
            $payload = $this->payload !== null ? $this->payload : file_get_contents('php://input');
        }

        try {
            $response = $this->requestFactory->createFromPayload($payload)->executeWith($this);
        } catch (JsonRpcException $e) {
            $response = RequestResponse::constructExceptionErrorResponse(null, $e);
        } catch (\Throwable $e) {
            $response = $this->handleException($e);
        }

        return new JsonResponse($response);
    }

    /**
     * @param string $payload
     * @param mixed $middlewareContext
     * @return JsonResponse
     */
    public function runWithPayload(string $payload, $middlewareContext = null): JsonResponse
    {
        return $this->run($middlewareContext, $payload);
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

            return $request->hasId() ? new RequestResponse($request->getId(), $result) : null;
        } catch (JsonRpcException $e) {
            return $request->hasId() ? RequestResponse::constructExceptionErrorResponse($request->getId(), $e) : null;
        } catch (\Throwable $e) {
            return $this->handleException($e, $request);
        }
    }

    /**
     * @param \Throwable $e
     * @param RequestInterface|null $request
     * @return RequestResponse|null
     */
    private function handleException(\Throwable $e, RequestInterface $request = null)
    {
        $handlerResult = $this->runExceptionHandlers($e, $request);

        if (!$handlerResult) {
            $this->logger->error($e);
        }

        if ($request && !$request->hasId()) {
            return null;
        }

        if ($handlerResult instanceof RequestResponse) {
            return $handlerResult;
        }

        return RequestResponse::constructExceptionErrorResponse(
            $request ? $request->getId() : null,
            new InternalErrorException()
        );
    }

    /**
     * @param \Throwable $e
     * @param RequestInterface $request
     * @return bool|RequestResponse
     */
    private function runExceptionHandlers(\Throwable $e, RequestInterface $request = null)
    {
        foreach ($this->exceptionHandlers as $className => $handler) {
            if ($e instanceof $className) {
                return $handler($e, $request);
            }
        }

        return false;
    }

}
