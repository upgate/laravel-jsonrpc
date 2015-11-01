<?php

namespace Upgate\LaravelJsonRpc\Contract;

interface MiddlewareDispatcher
{

    /**
     * @param MiddlewaresConfiguration $middlewares
     * @param $context
     * @param callable $next
     * @return mixed
     */
    public function dispatch(MiddlewaresConfiguration $middlewares, $context, callable $next);

}