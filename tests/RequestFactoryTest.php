<?php

use Upgate\LaravelJsonRpc\Exception\BadRequestException;
use Upgate\LaravelJsonRpc\Server\RequestFactory;

class RequestFactoryTest extends PHPUnit_Framework_TestCase
{

    public function testCreateRequestWithoutParameters()
    {
        $requestData = (object)[
            'jsonrpc' => '2.0',
            'method'  => 'foo'
        ];
        $requestFactory = new RequestFactory();
        $request = $requestFactory->createRequest($requestData);
        $this->assertEquals('foo', $request->getMethod());
        $this->assertNull($request->getParams());
    }

    public function testCreateRequestWithoutId()
    {
        $requestData = (object)[
            'jsonrpc' => '2.0',
            'method'  => 'foo'
        ];
        $requestFactory = new RequestFactory();
        $request = $requestFactory->createRequest($requestData);
        $this->assertNull($request->getId());
    }

    public function testCreateRequestWithNumericId()
    {
        $requestData = (object)[
            'jsonrpc' => '2.0',
            'method'  => 'foo',
            'id'      => 1
        ];
        $requestFactory = new RequestFactory();
        $request = $requestFactory->createRequest($requestData);
        $this->assertSame(1, $request->getId());
    }

    public function testCreateRequestWithStringId()
    {
        $requestData = (object)[
            'jsonrpc' => '2.0',
            'method'  => 'foo',
            'id'      => '1_1'
        ];
        $requestFactory = new RequestFactory();
        $request = $requestFactory->createRequest($requestData);
        $this->assertSame('1_1', $request->getId());
    }

    public function testCreateRequestWithPositionalParameters()
    {
        $requestData = (object)[
            'jsonrpc' => '2.0',
            'method'  => 'foo',
            'params'  => [1, "bar"]
        ];
        $requestFactory = new RequestFactory();
        $request = $requestFactory->createRequest($requestData);
        $this->assertEquals('foo', $request->getMethod());
        $this->assertEquals([1, "bar"], $request->getParams()->getParams());
        $this->assertFalse($request->getParams()->areParamsNamed());
    }

    public function testCreateRequestWithNamedParameters()
    {
        $requestData = (object)[
            'jsonrpc' => '2.0',
            'method'  => 'foo',
            'params'  => (object)['a' => 1, 'b' => "bar"]
        ];
        $requestFactory = new RequestFactory();
        $request = $requestFactory->createRequest($requestData);
        $this->assertEquals('foo', $request->getMethod());
        $this->assertEquals(['a' => 1, 'b' => "bar"], $request->getParams()->getParams());
        $this->assertTrue($request->getParams()->areParamsNamed());
    }

    public function testCreateRequestFailsWithBadVersion()
    {
        $requestData = (object)[
            'jsonrpc' => '1.0',
            'method'  => 'foo'
        ];
        $requestFactory = new RequestFactory();
        $this->setExpectedException(BadRequestException::class);
        $requestFactory->createRequest($requestData);
    }

    public function testCreateRequestFailsWithoutJsonRpcField()
    {
        $requestData = (object)[
            'method' => 'foo'
        ];
        $requestFactory = new RequestFactory();
        $this->setExpectedException(BadRequestException::class);
        $requestFactory->createRequest($requestData);
    }

    public function testCreateRequestWithoutMethod()
    {
        $requestData = (object)[
            'jsonrpc' => '2.0'
        ];
        $requestFactory = new RequestFactory();
        $this->setExpectedException(BadRequestException::class);
        $requestFactory->createRequest($requestData);
    }

    public function testCreateRequestFromPayload()
    {
        $requestData = (object)[
            'jsonrpc' => '2.0',
            'method'  => 'foo',
            'params'  => [1, "bar"],
            'id'      => 'foo',
        ];
        $requestFactory = new RequestFactory();
        $request = $requestFactory->createFromPayload(json_encode($requestData));
        $this->assertEquals('foo', $request->getMethod());
        $this->assertEquals([1, "bar"], $request->getParams()->getParams());
        $this->assertFalse($request->getParams()->areParamsNamed());
        $this->assertEquals('foo', $request->getId());
    }

    public function testCreateBatchFromPayload()
    {
        $requestData = [
            (object)[
                'jsonrpc' => '2.0',
                'method'  => 'foo',
                'id'      => 'foo',
            ],
            (object)[
                'jsonrpc' => '2.0',
                'method'  => 'bar',
                'id'      => 'bar',
            ],
        ];
        $requestFactory = new RequestFactory();
        $batch = $requestFactory->createFromPayload(json_encode($requestData));
        $this->assertInternalType('array', $batch);
        $this->assertCount(2, $batch);

        for ($i = 0; $i < 2; ++$i) {
            $request = $requestFactory->createRequest($batch[$i]);
            $this->assertEquals($requestData[$i]->method, $request->getMethod());
            $this->assertEquals($requestData[$i]->id, $request->getId());
        }
    }
}
