<?php
declare(strict_types=1);

use Upgate\LaravelJsonRpc\Server;

/**
 * Not a real unit-test, more like a functional library test
 */
class ServerFormRequestTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var Server\Server
     */
    private $server;

    public function testValidationPasses()
    {
        $this->server->setPayload(
            json_encode(
                [
                    'jsonrpc' => '2.0',
                    'method'  => 'test',
                    'id'      => 1,
                    'params'  => $params = ['id' => 1, 'email' => 'test@example.com']
                ]
            )
        );
        $response = $this->server->run();
        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'result'  => (object)$params,
            'id'      => 1
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

    public function testValidationFails()
    {
        $this->server->setPayload(
            json_encode(
                [
                    'jsonrpc' => '2.0',
                    'method'  => 'test',
                    'id'      => 1,
                    'params'  => ['email' => 'not an email']
                ]
            )
        );
        $response = $this->server->run();
        $expectedError = (object)[
            'code'              => Server\ErrorCode::INVALID_PARAMS,
            'message'           => 'Validation failed',
            'data' => (object)['violations' => (object)[
                'id'    => ['Required'],
                'email' => ['Bad Email']
            ]]
        ];
        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'id'      => 1,
            'error'   => $expectedError
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

    public function testValidationWithPositionalArgs()
    {
        $this->server->setPayload(
            json_encode(
                [
                    'jsonrpc' => '2.0',
                    'method'  => 'testPositional',
                    'id'      => 1,
                    'params'  => $params = [1, 'test@example.com']
                ]
            )
        );
        $response = $this->server->run();
        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'result'  => $params,
            'id'      => 1
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

    public function testValidationFailsWithPositionalArgs()
    {
        $this->server->setPayload(
            json_encode(
                [
                    'jsonrpc' => '2.0',
                    'method'  => 'testPositional',
                    'id'      => 1,
                    'params'  => ['not an id'],
                ]
            )
        );
        $response = $this->server->run();
        $expectedError = (object)[
            'code'              => Server\ErrorCode::INVALID_PARAMS,
            'message'           => 'Validation failed',
            'data' => (object)['violations' => [
                ['Not Numeric'],
                ['Required'],
            ]]
        ];
        $expectedResponseData = (object)[
            'jsonrpc' => '2.0',
            'id'      => 1,
            'error'   => $expectedError
        ];
        $this->assertEquals($expectedResponseData, json_decode($response->getContent()));
    }

    protected function setUp()
    {
        $container = new Illuminate\Container\Container();
        $container->bind(
            \Illuminate\Contracts\Translation\Translator::class,
            function () {
                $loader = new \Illuminate\Translation\ArrayLoader();
                $loader->addMessages(
                    'en',
                    'validation',
                    [
                        'required' => 'Required',
                        'email'    => 'Bad Email',
                        'numeric'  => 'Not Numeric',
                    ]
                );

                return new \Illuminate\Translation\Translator($loader, 'en');
            }
        );
        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMock();
        $this->server = new Server\Server(
            new Server\RequestFactory(),
            new Server\Router(),
            new Server\RouteDispatcher($container),
            new Server\MiddlewarePipelineDispatcher($container),
            $logger
        );
        $this->server->router()->bind('test', 'ServerFormRequestTest_Controller@test');
        $this->server->router()->bind('testPositional', 'ServerFormRequestTest_Controller@testPositional');
    }

}

class ServerFormRequestTest_FormRequest extends Server\FormRequest
{
    public function rules(): array
    {
        return [
            'id'    => 'required|numeric',
            'email' => 'required|email'
        ];
    }
}

class ServerFormRequestTest_PositionalFormRequest extends Server\FormRequest
{
    public function rules(): array
    {
        return [
            0 => 'required|numeric',
            1 => 'required|email'
        ];
    }
}

class ServerFormRequestTest_Controller
{
    public function test(ServerFormRequestTest_FormRequest $formRequest)
    {
        return $formRequest->all();
    }

    public function testPositional(ServerFormRequestTest_PositionalFormRequest $formRequest)
    {
        return $formRequest->all();
    }
}
