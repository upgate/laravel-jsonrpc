<?php
declare(strict_types=1);

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
    public function isEmpty(): bool
    {
        return 0 === count($this->middlewares);
    }

    /**
     * @param string $middleware
     * @return MiddlewaresConfigurationInterface
     */
    public function addMiddleware(string $middleware): MiddlewaresConfigurationInterface
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getMiddlewares(): array
    {
        return $this->aliasesRegistry ? $this->aliasesRegistry->resolveAliases($this->middlewares) : $this->middlewares;
    }

    /**
     * @param array $middlewares
     * @return MiddlewaresConfigurationInterface
     */
    public function setMiddlewares(array $middlewares = []): MiddlewaresConfigurationInterface
    {
        $this->middlewares = $middlewares;

        return $this;
    }

    /**
     * @param MiddlewareAliasRegistryInterface|null $aliases
     * @return MiddlewaresConfigurationInterface
     */
    public function setMiddlewareAliases(MiddlewareAliasRegistryInterface $aliases = null
    ): MiddlewaresConfigurationInterface {
        $this->aliasesRegistry = $aliases;

        return $this;
    }

}