<?php
declare(strict_types=1);

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
    public function __construct(
        string $controllerClass,
        string $actionName,
        MiddlewaresConfigurationInterface $middlewaresCollection
    ) {
        $this->controllerClass = $controllerClass;
        $this->actionName = $actionName;
        $this->middlewaresCollection = $middlewaresCollection;
    }

    /**
     * @return string
     */
    public function getControllerClass(): string
    {
        return $this->controllerClass;
    }

    /**
     * @return string
     */
    public function getActionName(): string
    {
        return $this->actionName;
    }

    /**
     * @return MiddlewaresConfigurationInterface
     */
    public function getMiddlewaresCollection(): MiddlewaresConfigurationInterface
    {
        return $this->middlewaresCollection;
    }

}