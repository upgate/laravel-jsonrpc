<?php
declare(strict_types=1);

use Upgate\LaravelJsonRpc\Server\MiddlewareAliasRegistry;

class MiddlewareAliasRegistryTest extends \PHPUnit\Framework\TestCase
{

    public function testMiddlewareAliasResolver(): void
    {
        $middlewareAliasRegistry = new MiddlewareAliasRegistry();
        $middlewareAliasRegistry->registerAliases(
            [
                'foo' => 'FooMiddleware',
                'baz' => 'BazMiddleware',
                'auth' => 'Authenticate',
            ]
        );

        $this->assertEquals('FooMiddleware', $middlewareAliasRegistry->findNameByAlias('foo'));

        $this->assertEquals('Authenticate', $middlewareAliasRegistry->findNameByAlias('auth'));
        $this->assertEquals('Authenticate:guard', $middlewareAliasRegistry->findNameByAlias('auth:guard'));

        $this->assertEquals(
            ['FooMiddleware', 'BarMiddleware', 'BazMiddleware', 'Authenticate', 'Authenticate:guard2', 'BazMiddleware:1,2'],
            $middlewareAliasRegistry->resolveAliases(['foo', 'BarMiddleware', 'baz', 'Authenticate', 'auth:guard2', 'baz:1,2'])
        );

        $this->expectException(\InvalidArgumentException::class);
        $middlewareAliasRegistry->findNameByAlias('does_not_exist');
    }

}
