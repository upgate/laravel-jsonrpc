<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Contract;

interface MiddlewareDispatcherInterface
{

    /**
     * @param MiddlewaresConfigurationInterface $middlewaresConfiguration
     * @param mixed $context
     * @param callable $next
     * @return mixed
     */
    public function dispatch(MiddlewaresConfigurationInterface $middlewaresConfiguration, $context, callable $next);

}