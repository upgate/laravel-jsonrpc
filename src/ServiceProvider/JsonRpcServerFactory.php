<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\ServiceProvider;

use Illuminate\Contracts\Container\Container;
use Upgate\LaravelJsonRpc\Contract\MiddlewareDispatcherInterface;
use Upgate\LaravelJsonRpc\Contract\RequestFactoryInterface;
use Upgate\LaravelJsonRpc\Contract\ServerInterface;
use Upgate\LaravelJsonRpc\Server\Server;
use Psr\Log\LoggerInterface;
use Upgate\LaravelJsonRpc\Server\RouteDispatcher;
use Upgate\LaravelJsonRpc\Server\MiddlewarePipelineDispatcher;
use Upgate\LaravelJsonRpc\Server\RequestFactory;
use Upgate\LaravelJsonRpc\Server\Router;

final class JsonRpcServerFactory
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @var RequestFactoryInterface|null
     */
    private $requestFactory = null;

    /**
     * @var MiddlewareDispatcherInterface|null
     */
    private $middlewareDispatcher = null;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function make(): ServerInterface
    {
        return new Server(
            $this->getRequestFactory(),
            new Router(),
            new RouteDispatcher($this->container),
            $this->getMiddlewareDispatcher(),
            $this->container->make(LoggerInterface::class)
        );
    }

    private function getRequestFactory(): RequestFactoryInterface
    {
        if (null === $this->requestFactory) {
            $this->requestFactory = new RequestFactory();
        }

        return $this->requestFactory;
    }

    private function getMiddlewareDispatcher(): MiddlewareDispatcherInterface
    {
        if (null === $this->middlewareDispatcher) {
            $this->middlewareDispatcher = new MiddlewarePipelineDispatcher($this->container);
        }

        return $this->middlewareDispatcher;
    }

}
