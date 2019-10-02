<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Contract;

use Upgate\LaravelJsonRpc\Server\RequestParams;

interface RequestInterface
{

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @return RequestParams
     */
    public function getParams(): RequestParams;

    /**
     * @return null|string|int
     */
    public function getId();

    /**
     * @return bool
     */
    public function hasId(): bool;

}
