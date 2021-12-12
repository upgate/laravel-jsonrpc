<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Contract;

interface MiddlewaresConfigurationInterface
{

    /**
     * @param MiddlewareAliasRegistryInterface|null $aliases
     * @return $this
     */
    public function setMiddlewareAliases(MiddlewareAliasRegistryInterface $aliases = null
    ): MiddlewaresConfigurationInterface;

    /**
     * @return string[]
     */
    public function getMiddlewares(): array;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @param array $middlewares
     * @return $this
     */
    public function setMiddlewares(array $middlewares = []): MiddlewaresConfigurationInterface;

    /**
     * @param string $middleware
     * @return $this
     */
    public function addMiddleware(string $middleware): MiddlewaresConfigurationInterface;

    /**
     * @param array $aliases
     * @return MiddlewaresConfigurationInterface
     */
    public function addMiddlewareAliases(array $aliases): MiddlewaresConfigurationInterface;

}
