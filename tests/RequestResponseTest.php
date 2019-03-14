<?php
declare(strict_types=1);

use Upgate\LaravelJsonRpc\Server\RequestResponse;
use Illuminate\Http\JsonResponse;

class RequestResponseTest extends \PHPUnit\Framework\TestCase
{

    public function testRequestResponseAcceptsJsonResponse()
    {
        if (!method_exists(JsonResponse::class,'getData')) {
            $this->markTestSkipped(JsonResponse::class . ' does not support getData()');
        }
        $id = 1;
        $response = new stdClass();
        $response->success = true;
        $jsonResponse = new JsonResponse($response);
        $requestResponse = new RequestResponse($id, $jsonResponse);
        $expected = ['id' => $id, 'jsonrpc' => '2.0', 'result' => $response];
        $this->assertEquals($expected, $requestResponse->toArray());
    }

}

