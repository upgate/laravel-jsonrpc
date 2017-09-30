<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Server;

abstract class Binding
{

    const DEFAULT_ACTION_NAME = 'index';

    private $middlewaresCollection;

    public function __construct(string $binding, MiddlewaresCollection $middlewaresCollection)
    {
        $this->setBinding($binding);
        $this->setMiddlewaresCollection($middlewaresCollection);
    }

    public function getMiddlewaresCollection(): MiddlewaresCollection
    {
        return $this->middlewaresCollection;
    }

    public function setMiddlewaresCollection(MiddlewaresCollection $middlewaresCollection)
    {
        $this->middlewaresCollection = $middlewaresCollection;

        return $this;
    }

    abstract public function resolveRoute(string $method): Route;

    abstract protected function setBinding(string $binding): void;

}