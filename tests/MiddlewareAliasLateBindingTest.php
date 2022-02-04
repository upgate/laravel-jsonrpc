<?php
declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Psr\Log\LoggerInterface;
use Upgate\LaravelJsonRpc\Server;
use PHPUnit\Framework\TestCase;

class MiddlewareAliasLateBindingTest extends TestCase
{

    /** @var Server\Server */
    private $server = null;

    protected function setUp(): void
    {
        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->method('make')->will(
            $this->returnCallback(
                function ($className) {
                    return new $className;
                }
            )
        );
        /** @var Container $container */

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        foreach (['emergency', 'alert', 'critical', 'error', 'log'] as $method) {
            $logger->method($method)->will(
                $this->returnCallback(function ($e) {
                    throw new Exception((string)$e);
                })
            );
        }
        /** @var LoggerInterface $logger */

        $this->server = new Server\Server(
            new Server\RequestFactory(),
            new Server\Router(),
            new Server\RouteDispatcher($container),
            new Server\MiddlewarePipelineDispatcher($container),
            $logger
        );
    }

    public function testLateBindingForGroup(): void
    {
        $this->server->router()
            ->group(['abort'], function (Server\Router $router) {
                $router->bindController('test', MiddlewareAliasLateBindingTest_Controller::class);
            });

        $this->server->registerMiddlewareAliases([
            'abort' => MiddlewareAliasLateBindingTest_Abort::class,
        ]);

        $response = $this->server->run(null, json_encode(
                [
                    'jsonrpc' => '2.0',
                    'method'  => 'test',
                    'id'      => 1
                ]
            )
        );

        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'result'  => (object)['aborted_by_middleware' => true],
            'id'      => 1
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

}

class MiddlewareAliasLateBindingTest_Controller {

    public function action() {
        return ['success' => true];
    }

}
class MiddlewareAliasLateBindingTest_Abort
{

    public function handle()
    {
        return ['aborted_by_middleware' => true];
    }

}
