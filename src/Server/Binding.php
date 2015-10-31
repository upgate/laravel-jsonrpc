<?php

namespace Upgate\LaravelJsonRpc\Server;

abstract class Binding
{

    const DEFAULT_ACTION_NAME = 'index';

    private $middlewaresCollection;

    public function __construct($binding, MiddlewaresCollection $middlewaresCollection)
    {
        $this->setBinding($binding);
        $this->setMiddlewaresCollection($middlewaresCollection);
    }

    public function setMiddlewaresCollection(MiddlewaresCollection $middlewaresCollection)
    {
        $this->middlewaresCollection = $middlewaresCollection;

        return $this;
    }

    public function getMiddlewaresCollection()
    {
        return $this->middlewaresCollection;
    }

    /**
     * @param string $method
     * @return Route
     */
    abstract public function resolveRoute($method);

    abstract protected function setBinding($binding);

}