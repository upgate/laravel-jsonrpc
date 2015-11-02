<?php

namespace Upgate\LaravelJsonRpc\Server;

use Upgate\LaravelJsonRpc\Contract\RequestFactoryInterface as RequestFactoryContract;
use Upgate\LaravelJsonRpc\Exception\BadRequestException;
use Upgate\LaravelJsonRpc\Contract\ExecutableInterface;

class RequestFactory implements RequestFactoryContract
{

    /**
     * @param string $payloadJson
     * @return ExecutableInterface
     */
    public function createFromPayload($payloadJson)
    {
        try {
            $payload = json_decode($payloadJson);
        } catch (\Exception $e) {
            throw new BadRequestException();
        }

        if (is_array($payload)) {
            if (!count($payload)) {
                throw new BadRequestException();
            }

            return new Batch($payload, $this);
        } elseif (is_object($payload)) {
            return $this->createRequest($payload);
        } else {
            throw new BadRequestException();
        }
    }

    /**
     * @param object $requestData
     * @return Request
     */
    public function createRequest($requestData)
    {
        if (!is_object($requestData)) {
            throw new BadRequestException();
        }
        if (!isset($requestData->jsonrpc) || $requestData->jsonrpc !== "2.0") {
            throw new BadRequestException();
        }
        if (empty($requestData->method) || !is_string($requestData->method)) {
            throw new BadRequestException();
        }
        $params = null;
        if (!empty($requestData->params)) {
            if (is_array($requestData->params)) {
                $params = RequestParams::constructPositional($requestData->params);
            } elseif (is_object($requestData->params)) {
                $params = RequestParams::constructNamed((array)$requestData->params);
            } else {
                throw new BadRequestException();
            }
        }

        return new Request(
            $requestData->method,
            $params,
            isset($requestData->id) ? $requestData->id : null
        );
    }

}