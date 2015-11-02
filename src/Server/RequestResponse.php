<?php

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

final class RequestResponse implements Jsonable, Arrayable
{

    private $id;
    private $result;
    private $isError = false;

    /**
     * @param string|int $id
     * @param \Exception $exception
     * @return RequestResponse
     */
    public static function constructExceptionErrorResponse($id, \Exception $exception)
    {
        return self::constructErrorResponse($id, $exception->getMessage(), $exception->getCode());
    }

    /**
     * @param string|int $id
     * @param string $message
     * @param int $code
     * @return RequestResponse
     */
    public static function constructErrorResponse($id, $message, $code = 0)
    {
        return new self(
            $id,
            [
                'code'    => $code ?: -32603,
                'message' => (string)$message
            ],
            true
        );
    }

    /**
     * @param string|int $id
     * @param mixed $result
     * @param bool $isError
     */
    public function __construct($id, $result, $isError = false)
    {
        $this->id = $id;
        $this->result = $result;
        $this->isError = (bool)$isError;
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