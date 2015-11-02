<?php

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class BatchResponse implements Jsonable, Arrayable
{

    private $responses = [];

    /**
     * @param Arrayable $response
     */
    public function add(Arrayable $response)
    {
        $this->responses[] = $response;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_map(
            function (Arrayable $response) {
                return (object)$response->toArray();
            },
            $this->responses
        );
    }

}