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
     * Check for existance of a parameter.
     *
     * @param int|string $key
     * @return bool whether parameter $key exists
     */
    public function has($key): bool
    {
        return array_key_exists($key, $this->params);
    }

    /**
     * Get a parameter by name or index.
     *
     * @param int|string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->params[$key] : $default;
    }

    /**
     * @param int|string $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __get($key)
    {
        if (!$this->has($key)) {
            throw new \InvalidArgumentException("Parameter does not exist: '$key'");
        }

        return $this->params[$key];
    }

    /**
     * @return bool
     */
    public function areParamsNamed(): bool
    {
        return $this->areParamsNamed;
    }

}