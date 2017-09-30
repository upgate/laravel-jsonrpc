<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Server;

final class MethodBinding extends Binding
{

    private $controllerClass;

    private $actionName;

    public function resolveRoute(string $method): Route
    {
        return new Route($this->controllerClass, $this->actionName, $this->getMiddlewaresCollection());
    }

    protected function setBinding(string $binding): void
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