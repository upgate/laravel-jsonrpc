<?php
declare(strict_types=1);

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Psr\Log\LoggerInterface;
use Upgate\LaravelJsonRpc\Contract\ServerInterface as JsonRpcServerContract;
use Upgate\LaravelJsonRpc\Server\Server;
use Upgate\LaravelJsonRpc\Server\ServerFactory;
use Upgate\LaravelJsonRpc\ServiceProvider\JsonRpcServerServiceProvider;

class ServiceProviderTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var Application
     */
    private $app;

    public function testServiceProviderProvidesServerAsSingleton()
    {
        $serviceProvider = new JsonRpcServerServiceProvider($this->app);
        $serviceProvider->register();

        $server1 = $this->app->make(JsonRpcServerContract::class);
        $this->assertInstanceOf(Server::class, $server1);
        $server2 = $this->app->make(JsonRpcServerContract::class);
        $this->assertInstanceOf(Server::class, $server2);
        $this->assertSame($server2, $server1);
    }

    public function testServiceProviderProvidesFactoryAsSingleton()
    {
        $serviceProvider = new JsonRpcServerServiceProvider($this->app);
        $serviceProvider->register();

        $factory1 = $this->app->make(ServerFactory::class);
        $this->assertInstanceOf(ServerFactory::class, $factory1);
        $factory2 = $this->app->make(ServerFactory::class);
        $this->assertInstanceOf(ServerFactory::class, $factory2);
        $this->assertSame($factory2, $factory1);
    }

    public function testProvidedFactoryMakesNewServer()
    {
        $serviceProvider = new JsonRpcServerServiceProvider($this->app);
        $serviceProvider->register();

        $server1 = $this->app->make(JsonRpcServerContract::class);
        $factory = $this->app->make(ServerFactory::class);
        $server2 = $factory->make();
        $this->assertNotSame($server2, $server1);
    }

    protected function setUp(): void
    {
        $container = new Container();
        $appMethods = array_map(function (ReflectionMethod $reflectionMethod) {
            return $reflectionMethod->getName();
        }, array_filter(
            (new ReflectionClass(Application::class))->getMethods(),
            function (ReflectionMethod $method) {
                return $method->isPublic() || $method->isAbstract();
            }
        ));
        $this->app = $this->getMockBuilder(Application::class);
        $this->app = $this->app
            ->onlyMethods($appMethods)
            ->enableProxyingToOriginalMethods()
            ->setProxyTarget($container)
            ->getMock();
        $container->instance(LoggerInterface::class, $this->getMockBuilder(LoggerInterface::class)->getMock());
        $container->singleton(JsonRpcServerContract::class, JsonRpcServerContract::class);
        $container->singleton(ServerFactory::class, ServerFactory::class);
    }

}
