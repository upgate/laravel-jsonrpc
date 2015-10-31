<?php

namespace Upgate\LaravelJsonRpc\Contract;

use Illuminate\Http\Request as HttpRequest;

interface RequestDispatcher
{

    /**
     * @param Route $route
     * @param Request $request
     * @param HttpRequest $httpRequest
     * @return mixed
     */
    public function dispatch(Route $route, Request $request, HttpRequest $httpRequest);

}