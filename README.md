# JSON-RPC Server for Laravel 5

[![Build Status](https://travis-ci.org/upgate/laravel-jsonrpc.svg?branch=master)](https://travis-ci.org/upgate/laravel-jsonrpc)

## Quick How-To

- Install with composer: `composer require upgate/laravel-jsonrpc`
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
    $jsonRpcServer->router()
        ->addMiddlewares(['fooMiddleware', 'barMiddleware']) // middleware alias names or class names
        ->bindController('foo', 'FooController') // for 'foo.$method' methods invoke FooController->$method(),
                                                 // for 'foo' method invoke FooConroller->index()
        ->bind('bar', 'MyController@bar') // for 'bar' method invoke MyController->bar()
        ->group(
            ['bazMiddleware'], // add bazMiddleware for methods in this group
            function ($jsonRpcRouter) {
                // for 'bar.baz' method invoke MyController->bazz()
                $jsonRpcRouter->bind('bar.baz', 'MyController@bazz');
            }
        );

    // Run json-rpc server with $request passed to middlewares as a handle() method argument
    return $jsonRpcServer->run($request);
});
```

See `ServerTest` and `RouterTest` for more examples.

## Exception handling

Descendants of `Upgate\LaravelJsonRpc\Exception\JsonRpcException` represent JSON-RPC errors according to the spec (see `Upgate\LaravelJsonRpc\Server\ErrorCode`).

Other exceptions are logged and converted to INTERNAL_ERROR responses by default.

Use `Upgate\LaravelJsonRpc\Server\Server::onException()` to register custom exception handlers:

```php
use Upgate\LaravelJsonRpc\Server;

$jsonRpcServer->onException(
    SomeExceptionClass::class,
    function (SomeExceptionClass $e, Server\Request $request) {
        $message = "Some Message";
        $code = -32099;
        return Server\RequestResponse::constructErrorResponse($request->getId(), $message, $code);
    }
);

$jsonRpcServer->onException(
    \Exception::class, // catch-all
    function (\Exception $e, Server\Request $request) {
        $message = "Some Other Message";
        $code = -32098;
        return Server\RequestResponse::constructErrorResponse($request->getId(), $message, $code);
    }
);
```

See `ServerTest:: testSingleRequestWithExceptionHandler()` and `ServerTest::testExceptionHandlersPriority()` for more examples.

## JSON-RPC Request Forms

(since v0.3.0)

`Upgate\LaravelJsonRpc\Server\FormRequest` is similar to Laravel form requests, but validates JSON-RPC parameters instead. 

Example:

```php
use Upgate\LaravelJsonRpc\Server\FormRequest as JsonRpcFormRequest;

class MyJsonRpcFormRequest extends JsonRpcFormRequest
{
    public function rules(): array
    {
        return [
            'id'    => 'required|numeric',
            'email' => 'required|email',
        ];
    }
}

class MyController
{
    public function myAction(MyJsonRpcFormRequest $jsonRpcRequest)
    {
        $email = $jsonRpcRequest->email; // or $jsonRpcRequest->get('email');
        $allParams = $jsonRpcRequest->all();
        // ...
    }
}
```

If validation fails, the INVALID_PARAMS error response is returned, with validation error details in `data.violations`.

See the `ServerFormRequestTest` for more examples.

## Compatibility

The library requires PHP 7.1 since v0.2.0. Unfortunately, PHP version constraint in composer.json was wrong in versions 0.2.0 - 0.2.1.
If you're using PHP 7.0, require `"upgate/laravel-jsonrpc": "^0.1"` in your composer.json.
