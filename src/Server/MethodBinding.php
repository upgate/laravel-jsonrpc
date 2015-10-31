<?php

namespace Upgate\LaravelJsonRpc\Server;

final class MethodBinding extends Binding
{

    private $controllerClass;

    private $actionName;

    /**
     * @param string $method
     * @return Route
     */
    public function resolveRoute($method)
    {
        return new Route($this->controllerClass, $this->actionName, $this->getMiddlewaresCollection());
    }

    protected function setBinding($binding)
    {
        $tokens = array_filter(explode('@', $binding, 2));
        switch (count($tokens)) {
            case 0:
                throw new \InvalidArgumentException('Empty binding');
                break;
            case 1:
                $this->controllerClass = $tokens[0];
                $this->actionName = self::DEFAULT_ACTION_NAME;
                break;
            case 2:
                $this->controllerClass = $tokens[0];
                $this->actionName = $tokens[1];
                break;
        }
    }

}