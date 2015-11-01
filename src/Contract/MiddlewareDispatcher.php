<?php

namespace Upgate\LaravelJsonRpc\Contract;

interface MiddlewareDispatcher
{

    /**
     * @param MiddlewaresConfiguration $middlewaresConfiguration
     * @param $context
     * @param callable $next
     * @return mixed
     */
    public function dispatch(MiddlewaresConfiguration $middlewaresConfiguration, $context, callable $next);

}