<?php

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Upgate\LaravelJsonRpc\Exception\JsonRpcException;

final class RequestResponse implements Jsonable, Arrayable
{

    private $id;
    private $result;
    private $isError = false;

    /**
     * @param string|int $id
     * @param JsonRpcException $exception
     * @return RequestResponse
     */
    public static function constructErrorResponse($id, JsonRpcException $exception)
    {
        $self = new self(
            $id,
            [
                'code'    => $exception->getCode(),
                'message' => $exception->getMessage()
            ]
        );
        $self->isError = true;

        return $self;
    }

    /**
     * @param string|int $id
     * @param mixed $result
     */
    public function __construct($id, $result)
    {
        $this->id = $id;
        $this->result = $result;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode((object)$this->toArray(), $options);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'jsonrpc'                             => '2.0',
            ($this->isError ? 'error' : 'result') => $this->result,
            'id'                                  => $this->id,
        ];
    }

}