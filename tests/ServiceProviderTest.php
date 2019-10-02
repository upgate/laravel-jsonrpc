<?php
declare(strict_types=1);

use Illuminate\Contracts\Foundation\Application;
use Psr\Log\LoggerInterface;
use Upgate\LaravelJsonRpc\Contract\ServerInterface as JsonRpcServerContract;
use Upgate\LaravelJsonRpc\Server\Server;
use Upgate\LaravelJsonRpc\ServiceProvider\JsonRpcServerFactory;
use Upgate\LaravelJsonRpc\ServiceProvider\JsonRpcServerServiceProvider;

class ServiceProviderTest extends \PHPUnit\Framework\TestCase
{

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

        $factory1 = $this->app->make(JsonRpcServerFactory::class);
        $this->assertInstanceOf(JsonRpcServerFactory::class, $factory1);
        $factory2 = $this->app->make(JsonRpcServerFactory::class);
        $this->assertInstanceOf(JsonRpcServerFactory::class, $factory2);
        $this->assertSame($factory2, $factory1);
    }

    public function testProvidedFactoryMakesNewServer()
    {
        $serviceProvider = new JsonRpcServerServiceProvider($this->app);
        $serviceProvider->register();

        $server1 = $this->app->make(JsonRpcServerContract::class);
        $factory = $this->app->make(JsonRpcServerFactory::class);
        $server2 = $factory->make();
        $this->assertNotSame($server2, $server1);
    }

    protected function setUp()
    {
        $this->app = new class extends Illuminate\Container\Container implements Application {
            public function version() {}
            public function basePath() {}
            public function environment() {}
            public function runningInConsole() {}
            public function runningUnitTests() {}
            public function isDownForMaintenance() {}
            public function registerConfiguredProviders() {}
            public function register($provider, $options = [], $force = false) {}
            public function registerDeferredProvider($provider, $service = null) {}
            public function boot() {}
            public function booting($callback) {}
            public function booted($callback) {}
            public function getCachedServicesPath() {}
            public function getCachedPackagesPath() {}
        };

        $this->app->instance(LoggerInterface::class, $this->getMockBuilder(LoggerInterface::class)->getMock());
    }

}
