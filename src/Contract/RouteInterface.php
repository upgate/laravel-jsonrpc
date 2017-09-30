<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Contract;

interface RouteInterface
{

    /**
     * @return string
     */
    public function getControllerClass(): string;

    /**
     * @return string
     */
    public function getActionName(): string;

    /**
     * @return MiddlewaresConfigurationInterface
     */
    public function getMiddlewaresCollection(): MiddlewaresConfigurationInterface;

}