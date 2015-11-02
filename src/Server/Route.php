<?php

namespace Upgate\LaravelJsonRpc\Server;

use Upgate\LaravelJsonRpc\Contract\MiddlewaresConfigurationInterface;
use Upgate\LaravelJsonRpc\Contract\RouteInterface;

final class Route implements RouteInterface
{

    private $controllerClass;
    private $actionName;
    private $middlewaresCollection;

    /**
     * @param string $controllerClass
     * @param string $actionName
     * @param MiddlewaresConfigurationInterface $middlewaresCollection
     */
    public function __construct($controllerClass, $actionName, MiddlewaresConfigurationInterface $middlewaresCollection)
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
     * @return MiddlewaresConfigurationInterface
     */
    public function getMiddlewaresCollection()
    {
        return $this->middlewaresCollection;
    }

}