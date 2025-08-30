<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Server;

abstract class FormRequest implements \ArrayAccess
{
    /**
     * @var RequestParams|null
     */
    private $params;

    /**
     * @param RequestParams|null $requestParams
     * @return $this
     */
    public function setRequestParams(?RequestParams $requestParams = null): FormRequest
    {
        $this->params = $requestParams;

        return $this;
    }

    /**
     * @return array of validation rules
     */
    abstract public function rules(): array;

    /**
     * @return array of custom messages for validation errors
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Get all parameters of a json-rpc request as an array.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->params ? $this->params->getParams() : [];
    }

    /**
     * @return array of custom attributes
     */
    public function attributes(): array
    {
        return [];
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
        return $this->params ? $this->params->get($key, $default) : $default;
    }

    public function has($key): bool
    {
        return $this->params ? $this->params->has($key) : false;
    }

    /**
     * @param int|string $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __get($key)
    {
        if (!$this->params) {
            throw new \InvalidArgumentException("Parameter does not exist: '$key'");
        }

        return $this->params->$key;
    }

    public function __isset($key)
    {
        return $this->has($key);
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        throw new \RuntimeException(get_called_class() . " is read-only");
    }

    public function offsetUnset($offset): void
    {
        throw new \RuntimeException(get_called_class() . " is read-only");
    }

}
