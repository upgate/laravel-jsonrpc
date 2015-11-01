<?php

namespace Upgate\LaravelJsonRpc\Contract;

use Upgate\LaravelJsonRpc\Server\RequestParams;

interface RouteDispatcher
{

    /**
     * @param Route $route
     * @param RequestParams $requestParams
     * @return mixed
     */
    public function dispatch(Route $route, RequestParams $requestParams = null);

}