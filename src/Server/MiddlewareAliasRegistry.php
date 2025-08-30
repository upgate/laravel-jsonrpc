<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Server;

use Upgate\LaravelJsonRpc\Contract\MiddlewareAliasRegistryInterface;

class MiddlewareAliasRegistry implements MiddlewareAliasRegistryInterface
{

    private $registry = [];

    public function __construct(?array $aliases = null)
    {
        if ($aliases) {
            $this->registerAliases($aliases);
        }
    }

    public function registerAlias(string $alias, string $name): MiddlewareAliasRegistryInterface
    {
        $this->registry[$alias] = (string)$name;

        return $this;
    }

    public function registerAliases(array $aliases, bool $replace = false): MiddlewareAliasRegistryInterface
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
     * @return string
     * @throws \InvalidArgumentException
     */
    public function findNameByAlias(string $alias): string
    {
        [$aliasName, $aliasParams] = $this->parseAlias($alias);

        if (!$this->aliasExists($aliasName)) {
            throw new \InvalidArgumentException("Middleware alias '$alias' has not been registered");
        }

        return $this->registry[$aliasName] . ($aliasParams === null ? '' : ":{$aliasParams}");
    }

    /**
     * @param array $middlewares
     * @return array
     */
    public function resolveAliases(array $middlewares): array
    {
        return array_map(
            function ($middleware) {
                return $this->aliasExists($middleware) ? $this->findNameByAlias($middleware) : $middleware;
            },
            $middlewares
        );
    }

    private function aliasExists(string $alias): bool
    {
        [$aliasName,] = $this->parseAlias($alias);

        return isset($this->registry[$aliasName]);
    }

    private function parseAlias(string $alias): array
    {
        $parts = explode(":", $alias, 2);

        return [$parts[0], $parts[1] ?? null];
    }
}
