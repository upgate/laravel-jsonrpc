<?php

namespace Upgate\LaravelJsonRpc\Server;

use Upgate\LaravelJsonRpc\Contract\MiddlewaresConfiguration;
use Upgate\LaravelJsonRpc\Contract\Route as RouteContract;

final class Route implements RouteContract
{

    private $controllerClass;
    private $actionName;
    private $middlewaresCollection;

    /**
     * @param string $controllerClass
     * @param string $actionName
     */
    public function __construct($controllerClass, $actionName, MiddlewaresConfiguration $middlewaresCollection)
    {
        $this->controllerClass = (string)$controllerClass;
        $this->actionName = (string)$actionName;
        $this->middlewaresCollection = $middlewaresCollection;
    }

    /**
     * @return string
     */
    public function getControllerClass()
    {
        return $this->controllerClass;
    }

    /**
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @return MiddlewaresConfiguration
     */
    public function getMiddlewaresCollection()
    {
        return $this->middlewaresCollection;
    }
}