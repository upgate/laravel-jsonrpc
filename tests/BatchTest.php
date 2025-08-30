<?php
declare(strict_types=1);

use Upgate\LaravelJsonRpc\Server\Batch;
use Upgate\LaravelJsonRpc\Contract\RequestFactoryInterface;
use Upgate\LaravelJsonRpc\Contract\RequestInterface;
use Upgate\LaravelJsonRpc\Contract\RequestExecutorInterface;
use Illuminate\Contracts\Support\Arrayable;

class BatchTest extends \PHPUnit\Framework\TestCase
{

    public function testBatch()
    {
        $requestFactory = $this->getMockBuilder(RequestFactoryInterface::class)->getMock();
        $mockRequestArgs = new \WeakMap();
        $requestFactory->method('createRequest')->willReturnCallback(
            function ($argument) use ($mockRequestArgs) {
                $request = $this->getMockBuilder(RequestInterface::class)->getMock();
                $mockRequestArgs[$request] = $argument;

                return $request;
            }
        );
        /** @var RequestFactoryInterface $requestFactory */

        $requestExecutor = $this->getMockBuilder(RequestExecutorInterface::class)->getMock();
        $requestExecutor->method('execute')->willReturnCallback(
            function ($requestMock) use ($mockRequestArgs) {
                $response = $this->getMockBuilder(Arrayable::class)->getMock();
                $response->method('toArray')->willReturn($mockRequestArgs[$requestMock]->params);

                return $response;
            }
        );
        /** @var RequestExecutorInterface $requestExecutor */

        $foo = (object)[
            'jsonrpc' => '2.0',
            'method'  => 'foo',
            'params'  => ['value' => 'foo'],
            'id'      => 'foo',
        ];
        $bar = (object)[
            'jsonrpc' => '2.0',
            'method'  => 'bar',
            'params'  => ['value' => 'bar'],
            'id'      => 'bar',
        ];

        $batch = new Batch([$foo, $bar], $requestFactory);
        $response = $batch->executeWith($requestExecutor);
        $responseJson = $response->toJson();
        $responseData = json_decode($responseJson);
        $expectedData = json_decode('[{"value":"foo"},{"value":"bar"}]');
        $this->assertEquals($expectedData, $responseData);
    }

}
