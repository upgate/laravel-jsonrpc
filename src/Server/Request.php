<?php

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Contracts\Support\Jsonable;
use Upgate\LaravelJsonRpc\Contract\ExecutableInterface;
use Upgate\LaravelJsonRpc\Contract\RequestInterface as RequestContract;
use Upgate\LaravelJsonRpc\Contract\RequestExecutorInterface;

class Request implements RequestContract, ExecutableInterface
{

    /**
     * @var string
     */
    private $method;

    /**
     * @var RequestParams|null
     */
    private $params;

    /**
     * @var null|string|int
     */
    private $id = null;

    /**
     * @param string $method
     * @param RequestParams|null $params
     * @param string|int|null $id
     */
    public function __construct($method, RequestParams $params = null, $id = null)
    {
        $this->method = (string)$method;
        $this->params = $params;
        if (null !== $id) {
            if (!is_int($id)) {
                $id = (string)$id;
            }
        }
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return RequestParams|null
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return null|string|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param RequestExecutorInterface $executor
     * @return Jsonable|null
     */
    public function executeWith(RequestExecutorInterface $executor)
    {
        return $executor->execute($this);
    }

}