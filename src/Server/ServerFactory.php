<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Contracts\Container\Container;
use Upgate\LaravelJsonRpc\Contract\MiddlewareDispatcherInterface;
use Upgate\LaravelJsonRpc\Contract\RequestFactoryInterface;
use Upgate\LaravelJsonRpc\Contract\ServerInterface;
use Psr\Log\LoggerInterface;

final class ServerFactory
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
