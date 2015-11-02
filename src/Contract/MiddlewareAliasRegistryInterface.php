<?php

namespace Upgate\LaravelJsonRpc\Contract;

interface MiddlewareAliasRegistryInterface
{

    /**
     * @param string $alias
     * @param string $name
     * @return $this
     */
    public function registerAlias($alias, $name);

    /**
     * @param array $aliases
     * @param bool $replace
     * @return $this
     */
    public function registerAliases(array $aliases, $replace = false);

    /**
     * @param string $alias
     * @return string|null
     */
    public function findNameByAlias($alias);

    /**
     * @param array $middlewares
     * @return array
     */
    public function resolveAliases(array $middlewares);

}