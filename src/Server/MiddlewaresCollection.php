<?php

namespace Upgate\LaravelJsonRpc\Server;

use Upgate\LaravelJsonRpc\Contract\MiddlewareAliasRegistryInterface;
use Upgate\LaravelJsonRpc\Contract\MiddlewaresConfigurationInterface;

final class MiddlewaresCollection implements MiddlewaresConfigurationInterface
{

    /**
     * @var array
     */
    private $middlewares;

    /**
     * @var MiddlewareAliasRegistryInterface
     */
    private $aliasesRegistry;

    /**
     * @param array $middlewares
     * @param MiddlewareAliasRegistryInterface $aliasesRegistry
     */
    public function __construct(array $middlewares = [], MiddlewareAliasRegistryInterface $aliasesRegistry = null)
    {
        $this->middlewares = $middlewares;
        $this->setMiddlewareAliases($aliasesRegistry);
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
        return $this->aliasesRegistry ? $this->aliasesRegistry->resolveAliases($this->middlewares) : $this->middlewares;
    }

    /**
     * @param MiddlewareAliasRegistryInterface|null $aliases
     * @return $this
     */
    public function setMiddlewareAliases(MiddlewareAliasRegistryInterface $aliases = null)
    {
        $this->aliasesRegistry = $aliases;
    }

}