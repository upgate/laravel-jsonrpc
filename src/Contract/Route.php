<?php

namespace Upgate\LaravelJsonRpc\Contract;

interface Route
{

    /**
     * @return string
     */
    public function getControllerClass();

    /**
     * @return string
     */
    public function getActionName();

    /**
     * @return MiddlewaresConfiguration
     */
    public function getMiddlewaresCollection();

}