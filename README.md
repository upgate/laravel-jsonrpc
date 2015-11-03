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
                // Create an instance of JsonRpcServer
                $jsonRpcServer = $this->app->make(JsonRpcServerContract::class);
                // Set default controller namespace
                $jsonRpcServer->setControllerNamespace($this->namespace);
                // Register middleware aliases configured for Laravel router
                $jsonRpcServer->registerMiddlewareAliases($router->getMiddleware());
                
                require app_path('Http/routes.php');
            }
        );
    }
}
```

- Use $jsonRpcServer in your routes.php, like this:

```php
$router->post('/jsonrpc', function (Illuminate\Http\Request $request) use ($jsonRpcServer) {
    return $jsonRpcServer->router()
        ->addMiddleware(['fooMiddleware', 'barMiddleware']) // middleware alias names or class names
        ->bindController('foo', 'FooController') // for 'foo.$method' methods invoke FooController->$method(),
                                                 // for 'foo' method invoke FooConroller->index()
        ->bind('bar', 'MyController@bar') // for 'bar' method invoke MyController->bar()
        ->group(
            ['bazMiddleware'], // add bazMiddleware for methods in this group
            function ($jsonRpcRouter) {
                $jsonRpcRouter->bind('bar.baz', 'MyController@bazz') // for 'bar.baz' method invoke MyController->bazz()
            }
        )
        ->run($request); // Run json-rpc server with $request passed to middlewares as a handle() method argument
});
```

See tests/ServerTest.php and tests/RouterTest.php for more examples.