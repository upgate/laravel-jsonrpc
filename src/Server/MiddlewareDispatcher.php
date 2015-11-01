<?php

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Contracts\Container\Container;
use Illuminate\Pipeline\Pipeline;
use Upgate\LaravelJsonRpc\Contract\MiddlewareDispatcher as MiddlewareDispatcherContract;
use Upgate\LaravelJsonRpc\Contract\MiddlewaresConfiguration;

final class MiddlewareDispatcher implements MiddlewareDispatcherContract
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
     * @param MiddlewaresConfiguration $middlewares
     * @param $context
     * @param callable $next
     * @return mixed
     */
    public function dispatch(MiddlewaresConfiguration $middlewares, $context, callable $next)
    {
        $pipeline = new Pipeline($this->container);

        return $pipeline->send($context)->through($middlewares)->then($next);
    }

}