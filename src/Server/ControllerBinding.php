<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Server;

final class ControllerBinding extends Binding
{

    private $controllerClass;

    private $defaultActionName;

    public function __construct(
        string $binding,
        MiddlewaresCollection $middlewaresCollection,
        string $defaultActionName = null
    ) {
        parent::__construct($binding, $middlewaresCollection);
        $this->defaultActionName = $defaultActionName;
    }

    public function resolveRoute(string $method): Route
    {
        $tokens = explode('.', $method, 2);
        if (!empty($tokens[1])) {
            $actionName = $tokens[1];
        } else {
            $actionName = $this->defaultActionName ?: self::DEFAULT_ACTION_NAME;
        }

        return new Route($this->controllerClass, $actionName, $this->getMiddlewaresCollection());
    }

    protected function setBinding(string $binding): void
    {
        $this->controllerClass = $binding;
    }

}