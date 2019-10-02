<?php
declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Psr\Log\LoggerInterface;
use Upgate\LaravelJsonRpc\Server;
use Upgate\LaravelJsonRpc\Server\ServerFactory;

class ServerFactoryTest extends \PHPUnit\Framework\TestCase
{

    public function testFactoryMakesNewInstancesOnEachCall()
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->method('make')->with(LoggerInterface::class)->willReturn($logger);
        /** @var Container $container */

        $factory = new ServerFactory($container);
        $server1 = $factory->make();
        $this->assertInstanceOf(Server\Server::class, $server1);
        $server2 = $factory->make();
        $this->assertInstanceOf(Server\Server::class, $server2);
        $this->assertNotSame($server2, $server1);
    }

}
