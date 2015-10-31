<?php

namespace Upgate\LaravelJsonRpc\Server;

final class RequestParams
{

    private $params;

    private $areParamsNamed;

    private function __construct(array $params, $areParamsNamed = false)
    {
        $this->params = $params;
        $this->areParamsNamed = (bool)$areParamsNamed;
    }

    public static function constructNamed(array $params)
    {
        return new self($params, true);
    }

    public static function constructPositional(array $params)
    {
        return new self(array_values($params), false);
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return bool
     */
    public function areParamsNamed()
    {
        return $this->areParamsNamed;
    }

}