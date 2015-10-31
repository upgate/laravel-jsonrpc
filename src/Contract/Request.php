<?php

namespace Upgate\LaravelJsonRpc\Contract;

use Upgate\LaravelJsonRpc\Server\RequestParams;

interface Request
{

    /**
     * @return string
     */
    public function getMethod();

    /**
     * @return RequestParams|null
     */
    public function getParams();

    /**
     * @return null|string|int
     */
    public function getId();

}