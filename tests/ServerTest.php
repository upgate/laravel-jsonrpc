<?php

use Illuminate\Contracts\Container\Container;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Upgate\LaravelJsonRpc\Contract\MiddlewaresConfigurationInterface;
use Upgate\LaravelJsonRpc\Server;

/**
 * Not a real unit-test, more like a functional library test
 */
class ServerTest extends PHPUnit_Framework_TestCase
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
            'result'  => (object)['aborted_by_middleware' => true],
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
                function (MiddlewaresConfigurationInterface $middlewares) {
                    $middlewares->addMiddleware(ServerTest_Middleware_DenyByContext::class);
                },
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

    private function assembleServer()
    {
        return new Server\Server(
            new Server\RequestFactory(),
            new Server\Router(),
            new Server\RouteDispatcher($this->constructContainerMock()),
            new Server\MiddlewareDispatcher($this->constructContainerMock()),
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

        return $container;
    }

    /**
     * @return LoggerInterface
     */
    private function constructLoggerMock()
    {
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

}

class ServerTest_BarController
{

    public function index()
    {
        return ['bar_index' => true];
    }

}

class ServerTest_Middleware_Abort
{

    public function handle()
    {
        return ['aborted_by_middleware' => true];
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
