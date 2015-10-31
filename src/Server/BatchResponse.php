<?php

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Contracts\Support\Jsonable;

class BatchResponse implements Jsonable
{

    private $responses = [];

    /**
     * @param RequestResponse $response
     */
    public function add(RequestResponse $response)
    {
        $this->responses = $response;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode(
            array_map(
                function (RequestResponse $response) {
                    return (object)$response->toArray();
                },
                $this->responses
            ),
            $options
        );
    }

}