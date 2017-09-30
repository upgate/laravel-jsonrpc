<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Contracts\Container\Container;
use Illuminate\Pipeline\Pipeline;
use Upgate\LaravelJsonRpc\Contract\MiddlewareDispatcherInterface;
use Upgate\LaravelJsonRpc\Contract\MiddlewaresConfigurationInterface;

final class MiddlewarePipelineDispatcher implements MiddlewareDispatcherInterface
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param MiddlewaresConfigurationInterface $middlewaresConfiguration
     * @param mixed $context
     * @param callable $next
     * @return mixed
     */
    public function dispatch(MiddlewaresConfigurationInterface $middlewaresConfiguration, $context, callable $next)
    {
        $pipeline = new Pipeline($this->container);

        return $pipeline->send($context)->through($middlewaresConfiguration->getMiddlewares())->then($next);
    }

}