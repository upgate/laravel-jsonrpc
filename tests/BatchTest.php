<?php

use Upgate\LaravelJsonRpc\Server\Batch;
use Upgate\LaravelJsonRpc\Contract\RequestFactoryInterface;
use Upgate\LaravelJsonRpc\Contract\RequestInterface;
use Upgate\LaravelJsonRpc\Contract\RequestExecutorInterface;
use Illuminate\Contracts\Support\Arrayable;

class BatchTest extends PHPUnit_Framework_TestCase
{

    public function testBatch()
    {
        $requestFactory = $this->getMockBuilder(RequestFactoryInterface::class)->getMock();
        $requestFactory->method('createRequest')->willReturnCallback(
            function ($argument) {
                $request = $this->getMockBuilder(RequestInterface::class)->getMock();
                /** @noinspection PhpUndefinedFieldInspection */
                $request->mockValue = $argument;

                return $request;
            }
        );
        /** @var RequestFactoryInterface $requestFactory */

        $requestExecutor = $this->getMockBuilder(RequestExecutorInterface::class)->getMock();
        $requestExecutor->method('execute')->willReturnCallback(
            function ($requestMock) {
                $response = $this->getMockBuilder(Arrayable::class)->getMock();
                $response->method('toArray')->willReturn(['value' => $requestMock->mockValue]);

                return $response;
            }
        );
        /** @var RequestExecutorInterface $requestExecutor */

        $batch = new Batch(['foo', 'bar'], $requestFactory);
        $response = $batch->executeWith($requestExecutor);
        $responseJson = $response->toJson();
        $responseData = json_decode($responseJson);
        $expectedData = json_decode('[{"value":"foo"},{"value":"bar"}]');
        $this->assertEquals($expectedData, $responseData);
    }

}
