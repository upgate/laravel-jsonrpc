<?php

namespace Upgate\LaravelJsonRpc\Server;

use Upgate\LaravelJsonRpc\Contract\MiddlewareAliasRegistryInterface;

class MiddlewareAliasRegistry implements MiddlewareAliasRegistryInterface
{

    private $registry = [];

    public function __construct(array $aliases = null)
    {
        if ($aliases) {
            $this->registerAliases($aliases);
        }
    }

    /**
     * @param string $alias
     * @param string $name
     * @return $this
     */
    public function registerAlias($alias, $name)
    {
        $this->registry[$alias] = (string)$name;

        return $this;
    }

    /**
     * @param array $aliases
     * @param bool $replace
     * @return $this
     */
    public function registerAliases(array $aliases, $replace = false)
    {
        if ($replace) {
            $this->registry = [];
        }

        foreach ($aliases as $alias => $name) {
            $this->registerAlias($alias, $name);
        }

        return $this;
    }

    /**
     * @param string $alias
     * @return string|null
     */
    public function findNameByAlias($alias)
    {
        return isset($this->registry[$alias]) ? $this->registry[$alias] : null;
    }

    /**
     * @param array $middlewares
     * @return array
     */
    public function resolveAliases(array $middlewares)
    {
        return array_map(
            function ($middleware) {
                return $this->findNameByAlias($middleware) ?: $middleware;
            },
            $middlewares
        );
    }
}