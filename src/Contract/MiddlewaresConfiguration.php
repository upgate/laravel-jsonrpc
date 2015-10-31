<?php

namespace Upgate\LaravelJsonRpc\Contract;

interface MiddlewaresConfiguration
{

    /**
     * @return string[]
     */
    public function getMiddlewares();

}