<?php

namespace Upgate\LaravelJsonRpc\ServiceProvider;

use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Upgate\LaravelJsonRpc\Contract\Server as JsonRpcServerContract;
use Upgate\LaravelJsonRpc\Server\RequestDispatcher;
use Upgate\LaravelJsonRpc\Server\RequestFactory;
use Upgate\LaravelJsonRpc\Server\Router;
use Upgate\LaravelJsonRpc\Server\Server;

class JsonRpcServerServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind(
            JsonRpcServerContract::class,
            function () {
                return new Server(
                    new RequestFactory(),
                    new Router(),
                    new RequestDispatcher($this->app),
                    $this->app->make(LoggerInterface::class)
                );
            }
        );
    }

}