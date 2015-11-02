<?php

namespace Upgate\LaravelJsonRpc\Contract;

interface RouteInterface
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
     * @return MiddlewaresConfigurationInterface
     */
    public function getMiddlewaresCollection();

}