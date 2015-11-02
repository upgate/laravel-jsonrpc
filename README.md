# JSON-RPC Server for Laravel 5.1

## Quick How-To

- Install with composer
- Add 'Upgate\LaravelJsonRpc\ServiceProvider\JsonRpcServerServiceProvider' to the service providers list
- In your RouteServiceProvider, do something like this:


```php
// ...
use Upgate\LaravelJsonRpc\Contract\ServerInterface as JsonRpcServerContract;

class RouteServiceProvider extends ServiceProvider
{
    // ...
    public function map(Router $router)
    {
        $router->group(
            ['namespace' => $this->namespace],
            function (Router $router) {
                $jsonRpcServer = $this->app->make(JsonRpcServerContract::class);
                $jsonRpcServer->setControllerNamespace($this->namespace);
                $jsonRpcServer->registerMiddlewareAliases($router->getMiddleware());
                require app_path('Http/routes.php');
            }
        );
    }
}
```

- Use $jsonRpcServer in your routes.php, like this:

```php
$router->post('/jsonrpc', function(Illuminate\Http\Request $request) use ($jsonRpcServer) {
    return $jsonRpcServer->router()
        ->addMiddleware(['foo', 'bar'])
        ->bindController('foo', 'FooController')
        ->bind('bar', 'MyController@bar')
        ->group(
            function ($middlewares) {
                $middlewares->addMiddleware('baz');
            },
            function ($jsonRpcRouter) {
                $jsonRpcRouter->bind('bar.baz', 'MyController@bazz')
            }
        )
        ->run($request);
});
```

See tests/ServerTest.php and tests/RouterTest.php for more examples.