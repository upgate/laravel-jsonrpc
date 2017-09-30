<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Server;

final class RequestParams
{

    private $params;

    private $areParamsNamed;

    private function __construct(array $params, bool $areParamsNamed = false)
    {
        $this->params = $params;
        $this->areParamsNamed = $areParamsNamed;
    }

    public static function constructNamed(array $params)
    {
        return new self($params, true);
    }

    public static function constructPositional(array $params)
    {
        return new self(array_values($params), false);
    }

    public static function constructEmpty()
    {
        return new self([]);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return bool
     */
    public function areParamsNamed(): bool
    {
        return $this->areParamsNamed;
    }

}