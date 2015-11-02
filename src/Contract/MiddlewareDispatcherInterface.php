<?php

namespace Upgate\LaravelJsonRpc\Contract;

interface MiddlewareDispatcherInterface
{

    /**
     * @param MiddlewaresConfigurationInterface $middlewaresConfiguration
     * @param $context
     * @param callable $next
     * @return mixed
     */
    public function dispatch(MiddlewaresConfigurationInterface $middlewaresConfiguration, $context, callable $next);

}