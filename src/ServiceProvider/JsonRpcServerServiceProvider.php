<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\ServiceProvider;

use Illuminate\Support\ServiceProvider;
use Upgate\LaravelJsonRpc\Contract\ServerInterface as JsonRpcServerContract;

class JsonRpcServerServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton(
            JsonRpcServerFactory::class,
            function () {
                return new JsonRpcServerFactory($this->app);
            }
        );

        $this->app->singleton(
            JsonRpcServerContract::class,
            function () {
                return $this->app->make(JsonRpcServerFactory::class)->make();
            }
        );
    }

}
