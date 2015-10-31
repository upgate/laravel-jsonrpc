<?php

namespace Upgate\LaravelJsonRpc\Server;

final class ControllerBinding extends Binding
{

    private $controllerClass;

    private $defaultActionName;

    public function __construct($binding, MiddlewaresCollection $middlewaresCollection, $defaultActionName = null)
    {
        parent::__construct($binding, $middlewaresCollection);
        $this->defaultActionName = $defaultActionName ? (string)$defaultActionName : null;
    }

    /**
     * @param string $method
     * @return Route
     */
    public function resolveRoute($method)
    {
        $tokens = explode('.', $method, 2);
        if (!empty($tokens[1])) {
            $actionName = $tokens[1];
        } else {
            $actionName = $this->defaultActionName ?: self::DEFAULT_ACTION_NAME;
        }

        return new Route($this->controllerClass, $actionName, $this->getMiddlewaresCollection());
    }

    protected function setBinding($binding)
    {
        $this->controllerClass = $binding;
    }

}