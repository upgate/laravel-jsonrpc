<?php
declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Upgate\LaravelJsonRpc\Server\MiddlewarePipelineDispatcher;
use Upgate\LaravelJsonRpc\Contract\MiddlewaresConfigurationInterface;

class MiddlewareDispatcherTest extends \PHPUnit\Framework\TestCase
{

    public function testNoMiddlewares()
    {
        $container = $this->getMockBuilder(Container::class)->getMock();
        /** @var Container $container */

        $middlewares = $this->getMockBuilder(MiddlewaresConfigurationInterface::class)->getMock();
        $middlewares->method('getMiddlewares')->willReturn([]);
        /** @var MiddlewaresConfigurationInterface $middlewares */

        $middlewareDispatcher = new MiddlewarePipelineDispatcher($container);
        $result = $middlewareDispatcher->dispatch(
            $middlewares,
            42,
            function ($context) {
                return $context;
            }
        );
        $this->assertEquals(42, $result);
    }

    public function testMiddlewaresChain()
    {
        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->method('make')->will(
            $this->returnCallback(
                function ($className) {
                    return new $className;
                }
            )
        );
        /** @var Container $container */

        $middlewares = $this->getMockBuilder(MiddlewaresConfigurationInterface::class)->getMock();
        $middlewares->method('getMiddlewares')->willReturn([
            MiddlewareDispatcherTest_MiddlewareIncrement::class,
            MiddlewareDispatcherTest_MiddlewareDouble::class,
        ]);
        /** @var MiddlewaresConfigurationInterface $middlewares */

        $middlewareDispatcher = new MiddlewarePipelineDispatcher($container);
        $result = $middlewareDispatcher->dispatch(
            $middlewares,
            42,
            function ($context) {
                return -$context;
            }
        );
        $this->assertEquals($result, -(42 + 1) * 2);
    }

    public function testMiddlewaresAbortedChain()
    {
        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->method('make')->will(
            $this->returnCallback(
                function ($className) {
                    return new $className;
                }
            )
        );
        /** @var Container $container */

        $middlewares = $this->getMockBuilder(MiddlewaresConfigurationInterface::class)->getMock();
        $middlewares->method('getMiddlewares')->willReturn([
            MiddlewareDispatcherTest_MiddlewareIncrement::class,
            MiddlewareDispatcherTest_MiddlewareReturn::class,
            MiddlewareDispatcherTest_MiddlewareException::class,
        ]);
        /** @var MiddlewaresConfigurationInterface $middlewares */

        $middlewareDispatcher = new MiddlewarePipelineDispatcher($container);
        $result = $middlewareDispatcher->dispatch(
            $middlewares,
            42,
            function ($context) {
                throw new \LogicException('Must never reach here. Context: ' . $context);
            }
        );
        $this->assertEquals($result, 42 + 1);
    }

}

class MiddlewareDispatcherTest_MiddlewareIncrement {

    public function handle($context, callable $next) {
        return $next($context + 1);
    }

}

class MiddlewareDispatcherTest_MiddlewareDouble {

    public function handle($context, callable $next) {
        return $next($context * 2);
    }

}

class MiddlewareDispatcherTest_MiddlewareReturn {

    public function handle($context) {
        return $context;
    }

}

class MiddlewareDispatcherTest_MiddlewareException {

    public function handle() {
        throw new \LogicException(__CLASS__ . ' reached');
    }

}
