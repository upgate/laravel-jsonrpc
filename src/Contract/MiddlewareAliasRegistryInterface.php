<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Contract;

interface MiddlewareAliasRegistryInterface
{

    /**
     * @param string $alias
     * @param string $name
     * @return $this
     */
    public function registerAlias(string $alias, string $name): MiddlewareAliasRegistryInterface;

    /**
     * @param array $aliases
     * @param bool $replace
     * @return $this
     */
    public function registerAliases(array $aliases, bool $replace = false): MiddlewareAliasRegistryInterface;

    /**
     * @param string $alias
     * @return string
     * @throws \InvalidArgumentException
     */
    public function findNameByAlias(string $alias): string;

    /**
     * @param string[] $middlewares
     * @return array
     */
    public function resolveAliases(array $middlewares): array;

}