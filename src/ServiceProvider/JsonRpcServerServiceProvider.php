<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\ServiceProvider;

use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Upgate\LaravelJsonRpc\Contract\ServerInterface as JsonRpcServerContract;
use Upgate\LaravelJsonRpc\Server\RouteDispatcher;
use Upgate\LaravelJsonRpc\Server\MiddlewarePipelineDispatcher;
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
                    new RouteDispatcher($this->app),
                    new MiddlewarePipelineDispatcher($this->app),
                    $this->app->make(LoggerInterface::class)
                );
            }
        );
    }

}