<?php
declare(strict_types=1);

use Upgate\LaravelJsonRpc\Server\MiddlewareAliasRegistry;
use Upgate\LaravelJsonRpc\Contract\MiddlewaresConfigurationInterface;

class MiddlewareAliasRegistryTest extends \PHPUnit\Framework\TestCase
{

    public function testMiddlewareAliasResolver()
    {
        /** @var MiddlewaresConfigurationInterface $middlewaresConfiguration */

        $middlewareAliasRegistry = new MiddlewareAliasRegistry();
        $middlewareAliasRegistry->registerAliases(
            [
                'foo' => 'FooMiddleware',
                'baz' => 'BazMiddleware'
            ]
        );

        $this->assertEquals('FooMiddleware', $middlewareAliasRegistry->findNameByAlias('foo'));

        $this->assertEquals(
            ['FooMiddleware', 'BarMiddleware', 'BazMiddleware'],
            $middlewareAliasRegistry->resolveAliases(['foo', 'BarMiddleware', 'baz'])
        );

        $this->expectException(\InvalidArgumentException::class);
        $middlewareAliasRegistry->findNameByAlias('does_not_exist');
    }

}
