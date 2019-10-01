<?php
declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Upgate\LaravelJsonRpc\Server;

/**
 * Not a real unit-test, more like a functional library test
 */
class ServerTest extends \PHPUnit\Framework\TestCase
{

    public function testSingleRequest()
    {
        $server = $this->assembleServer();

        $server->router()->bindController('foo', 'ServerTest_FooController');

        $server->setPayload(
            json_encode(
                [
                    'jsonrpc' => '2.0',
                    'method'  => 'foo',
                    'id'      => 1
                ]
            )
        );

        $response = $server->run();

        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'result'  => (object)['foo_index' => true],
            'id'      => 1
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

    public function testSingleRequestWithMiddlewareResponse()
    {
        $server = $this->assembleServer();

        $server->router()
            ->addMiddleware(ServerTest_Middleware_Abort::class)
            ->bindController('foo', 'ServerTest_FooController');

        $server->setPayload(
            json_encode(
                [
                    'jsonrpc' => '2.0',
                    'method'  => 'foo',
                    'id'      => 1
                ]
            )
        );

        $response = $server->run();

        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'result'  => (object)['aborted_by_middleware' => true, 'context' => null],
            'id'      => 1
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

    public function testSingleRequestWithMiddlewareAlias()
    {
        $server = $this->assembleServer();

        $server->registerMiddlewareAliases(['abort' => ServerTest_Middleware_Abort::class]);

        $server->router()
            ->addMiddleware('abort')
            ->bindController('foo', 'ServerTest_FooController');

        $server->setPayload(
            json_encode(
                [
                    'jsonrpc' => '2.0',
                    'method'  => 'foo',
                    'id'      => 1
                ]
            )
        );

        $response = $server->run();

        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'result'  => (object)['aborted_by_middleware' => true, 'context' => null],
            'id'      => 1
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

    public function testSingleRequestWithMultipleMiddlewareAliases()
    {
        $server = $this->assembleServer();

        $server->registerMiddlewareAliases(
            [
                'foo' => ServerTest_Middleware_AddFoo::class,
                'bar' => ServerTest_Middleware_AddBar::class,
                'abort' => ServerTest_Middleware_Abort::class,
            ]
        );

        $server->router()
            ->addMiddlewares(['foo', 'bar', 'abort'])
            ->bindController('foo', 'ServerTest_FooController');

        $server->setPayload(
            json_encode(
                [
                    'jsonrpc' => '2.0',
                    'method'  => 'foo',
                    'id'      => 1
                ]
            )
        );

        $response = $server->run();

        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'result'  => (object)['aborted_by_middleware' => true, 'context' => (object)['foo' => true, 'bar' => true]],
            'id'      => 1
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

    public function testSingleRequestWithExceptionThrown()
    {
        $server = $this->assembleServer();

        $server->router()->bindController('foo', 'ServerTest_FooController');

        $server->setPayload(
            json_encode(
                [
                    'jsonrpc' => '2.0',
                    'method'  => 'foo.throwException',
                    'id'      => 1
                ]
            )
        );

        $response = $server->run();

        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'error'   => (object)['message' => 'Internal error', 'code' => -32603],
            'id'      => 1
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

    public function testSingleRequestWithExceptionHandler()
    {
        $server = $this->assembleServer();

        $server->router()
            ->addMiddleware(ServerTest_Middleware_Deny::class)
            ->bindController('foo', 'ServerTest_FooController');

        $server->onException(
            AccessDeniedHttpException::class,
            function (\Exception $e, Server\Request $request) {
                return Server\RequestResponse::constructErrorResponse($request->getId(), $e->getMessage(), 403);
            }
        );

        $server->setPayload(
            json_encode(
                [
                    'jsonrpc' => '2.0',
                    'method'  => 'foo',
                    'id'      => 1
                ]
            )
        );

        $response = $server->run();

        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'error'   => (object)['message' => 'Access Denied', 'code' => 403],
            'id'      => 1
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

    public function testThrowableFromMiddleware()
    {
        $server = $this->assembleServer();

        $server->router()
            ->addMiddleware(ServerTest_Middleware_ThrowsCatchableFatal::class)
            ->bindController('foo', 'ServerTest_FooController');

        /** @var \Throwable $throwable */
        $throwable = null;

        $server->onException(
            Throwable::class,
            function (\Throwable $e, Server\Request $request = null) use (&$throwable) {
                $throwable = $e;
                return Server\RequestResponse::constructErrorResponse($request ? $request->getId() : null, $e->getMessage(), $e->getCode());
            }
        );

        $server->setPayload(
            json_encode(
                [
                    'jsonrpc' => '2.0',
                    'method'  => 'foo',
                    'id'      => 1
                ]
            )
        );

        $response = $server->run();
        $this->assertInstanceOf(\Throwable::class, $throwable);

        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'error'   => (object)['message' => $throwable->getMessage(), 'code' => $throwable->getCode()],
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }


    public function testThrowableFromController()
    {
        $server = $this->assembleServer();

        $server->router()
            ->bindController('foo', 'ServerTest_FooController');

        /** @var \Throwable $throwable */
        $throwable = null;

        $server->onException(
            Throwable::class,
            function (\Throwable $e, Server\Request $request = null) use (&$throwable) {
                $throwable = $e;
                return Server\RequestResponse::constructErrorResponse($request ? $request->getId() : null, $e->getMessage(), $e->getCode());
            }
        );

        $server->setPayload(
            json_encode(
                [
                    'jsonrpc' => '2.0',
                    'method'  => 'foo.throwCatchableFatal',
                    'id'      => 1
                ]
            )
        );

        $response = $server->run();
        $this->assertInstanceOf(\Throwable::class, $throwable);

        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'error'   => (object)['message' => $throwable->getMessage(), 'code' => $throwable->getCode()],
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

    public function testExceptionHandlersPriority()
    {
        $server = $this->assembleServer();

        $server->router()
            ->addMiddleware(ServerTest_Middleware_Deny::class)
            ->bindController('foo', 'ServerTest_FooController');

        /** @noinspection PhpUnusedParameterInspection */
        $server->onException(
            \Exception::class,
            function (\Exception $_, Server\Request $request) {
                return Server\RequestResponse::constructErrorResponse($request->getId(), 'Error', -32999);
            }
        );

        $server->onException(
            AccessDeniedHttpException::class,
            function (\Exception $e, Server\Request $request) {
                return Server\RequestResponse::constructErrorResponse($request->getId(), $e->getMessage(), 403);
            },
            $first = true
        );

        $server->setPayload(
            json_encode(
                [
                    'jsonrpc' => '2.0',
                    'method'  => 'foo',
                    'id'      => 1
                ]
            )
        );

        $response = $server->run();

        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'error'   => (object)['message' => 'Access Denied', 'code' => 403],
            'id'      => 1
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

    public function testBatchRequest()
    {
        $server = $this->assembleServer();

        $server->router()
            ->bindController('foo', ServerTest_FooController::class)
            ->group(
                ServerTest_Middleware_DenyByContext::class,
                function (Server\Router $router) {
                    $router->bind('bar', ServerTest_BarController::class);
                }
            );

        $server->onException(
            AccessDeniedHttpException::class,
            function (\Exception $e, Server\Request $request) {
                return Server\RequestResponse::constructErrorResponse($request->getId(), $e->getMessage(), 403);
            }
        );

        $server->setPayload(
            json_encode(
                [
                    [
                        'jsonrpc' => '2.0',
                        'method'  => 'foo',
                        'id'      => 1
                    ],
                    [
                        'jsonrpc' => '2.0',
                        'method'  => 'foo.foo',
                        'id'      => 2
                    ],
                    [
                        'jsonrpc' => '2.0',
                        'method'  => 'bar',
                        'id'      => 3
                    ],
                ]
            )
        );

        $context = new stdClass();
        $context->deny = true;

        $response = $server->run($context);

        $expectedResponseData = [
            (object)[
                'jsonrpc' => '2.0',
                'result'  => (object)['foo_index' => true],
                'id'      => 1
            ],
            (object)[
                'jsonrpc' => '2.0',
                'result'  => (object)['foo_foo' => true],
                'id'      => 2
            ],
            (object)[
                'jsonrpc' => '2.0',
                'error'   => (object)['message' => 'Access Denied By Context', 'code' => 403],
                'id'      => 3
            ],
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

    public function testControllerNamespace()
    {
        eval(
        '
            namespace ServerTestNs {
                class ServerTest_FooController {
                    public function index() {
                        return ["ns_foo_index" => true];
                    }
                }
            }
        '
        );

        $server = $this->assembleServer();

        $server->setControllerNamespace('ServerTestNs');

        $server->router()->bindController('foo', 'ServerTest_FooController');

        $server->setPayload(
            json_encode(
                [
                    'jsonrpc' => '2.0',
                    'method'  => 'foo',
                    'id'      => 1
                ]
            )
        );

        $response = $server->run();

        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'result'  => (object)['ns_foo_index' => true],
            'id'      => 1
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

    public function testMalformedPayloadExceptionIsHandled()
    {
        $server = $this->assembleServer();
        $server->setPayload('');
        $response = $server->run();
        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'error'   => (object)['message' => 'Invalid Request', 'code' => -32600]
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

    private function assembleServer()
    {
        return new Server\Server(
            new Server\RequestFactory(),
            new Server\Router(),
            new Server\RouteDispatcher($this->constructContainerMock()),
            new Server\MiddlewarePipelineDispatcher($this->constructContainerMock()),
            $this->constructLoggerMock()
        );
    }

    /**
     * @return Container
     */
    private function constructContainerMock()
    {
        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->method('make')->will(
            $this->returnCallback(
                function ($className) {
                    return new $className;
                }
            )
        );

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $container;
    }

    /**
     * @return LoggerInterface
     */
    private function constructLoggerMock()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getMockBuilder(LoggerInterface::class)->getMock();
    }

}

class ServerTest_FooController
{

    public function index()
    {
        return ['foo_index' => true];
    }

    public function foo()
    {
        return ['foo_foo' => true];
    }

    public function throwException()
    {
        throw new \RuntimeException();
    }

    public function throwCatchableFatal()
    {
        $a = null;
        /** @noinspection PhpUndefinedMethodInspection */
        $a->noSuchMethod();
    }

}

class ServerTest_BarController
{

    public function index()
    {
        return ['bar_index' => true];
    }

}

class ServerTest_Middleware_AddFoo
{

    public function handle($context, callable $next)
    {
        if (!is_object($context)) {
            $context = new \stdClass;
        }
        $context->foo = true;

        return $next($context);
    }

}

class ServerTest_Middleware_AddBar
{

    public function handle($context, callable $next)
    {
        if (!is_object($context)) {
            $context = new \stdClass;
        }
        $context->bar = true;

        return $next($context);
    }

}

class ServerTest_Middleware_Abort
{

    public function handle($context)
    {
        return ['aborted_by_middleware' => true, 'context' => $context];
    }

}

class ServerTest_Middleware_Deny
{

    public function handle()
    {
        throw new AccessDeniedHttpException('Access Denied');
    }

}

class ServerTest_Middleware_DenyByContext
{

    public function handle($context, callable $next)
    {
        if (is_object($context) && isset($context->deny)) {
            throw new AccessDeniedHttpException('Access Denied By Context');
        }

        return $next($context);
    }

}

class ServerTest_Middleware_ThrowsCatchableFatal
{

    public function handle($context, callable $next)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $context->noSuchMethod();

        return $next($context);
    }

}
