<?php

namespace Upgate\LaravelJsonRpc\Server;

use Upgate\LaravelJsonRpc\Contract\MiddlewaresConfigurationInterface;

final class MiddlewaresCollection implements MiddlewaresConfigurationInterface
{

    /**
     * @var array
     */
    private $middlewares;

    /**
     * @param array $middlewares
     */
    public function __construct(array $middlewares = [])
    {
        $this->middlewares = $middlewares;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return 0 === count($this->middlewares);
    }

    /**
     * @param array $middlewares
     * @return $this
     */
    public function setMiddlewares(array $middlewares = [])
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * @param string $middleware
     * @return $this
     */
    public function addMiddleware($middleware)
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

}