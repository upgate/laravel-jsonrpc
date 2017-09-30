<?php
declare(strict_types=1);

use Upgate\LaravelJsonRpc\Exception\RouteNotFoundException;
use Upgate\LaravelJsonRpc\Server\MiddlewaresCollection;
use Upgate\LaravelJsonRpc\Server\Router;

class RouterTest extends \PHPUnit\Framework\TestCase
{

    public function testMethodBinding()
    {
        $router = new Router();
        $router->bind('foo', 'FooController@bar');
        $route = $router->resolve('foo');
        $this->assertEquals('FooController', $route->getControllerClass());
        $this->assertEquals('bar', $route->getActionName());
    }

    public function testMethodBindingDefaultAction()
    {
        $router = new Router();
        $router->bind('foo', 'FooController');
        $route = $router->resolve('foo');
        $this->assertEquals('FooController', $route->getControllerClass());
        $this->assertEquals('index', $route->getActionName());
    }

    public function testControllerBinding()
    {
        $router = new Router();
        $router->bindController('foo', 'FooController');
        $route = $router->resolve('foo.bar');
        $this->assertEquals('FooController', $route->getControllerClass());
        $this->assertEquals('bar', $route->getActionName());
    }

    public function testControllerBindingDefaultAction()
    {
        $router = new Router();
        $router->bindController('foo', 'FooController');
        $route = $router->resolve('foo');
        $this->assertEquals('FooController', $route->getControllerClass());
        $this->assertEquals('index', $route->getActionName());
    }

    public function testMethodBindingPriority()
    {
        $router = new Router();
        $router->bindController('foo', 'FooController');
        $router->bind('foo.override', 'BarController@baz');
        $route = $router->resolve('foo.bar');
        $this->assertEquals('FooController', $route->getControllerClass());
        $this->assertEquals('bar', $route->getActionName());
        $route = $router->resolve('foo.override');
        $this->assertEquals('BarController', $route->getControllerClass());
        $this->assertEquals('baz', $route->getActionName());
    }

    public function testBindingNotFound()
    {
        $router = new Router();
        $this->expectException(RouteNotFoundException::class);
        $router->resolve('foo');
    }

    public function testGroupMerge()
    {
        $router = new Router();
        $router->bind('foo', 'FooController@foo');
        $router->group(
            null,
            function (Router $router) {
                $router->bind('bar', 'BarController@bar');
            }
        );
        $fooRoute = $router->resolve('foo');
        $barRoute = $router->resolve('bar');
        $this->assertEquals('FooController', $fooRoute->getControllerClass());
        $this->assertEquals('foo', $fooRoute->getActionName());
        $this->assertEquals('BarController', $barRoute->getControllerClass());
        $this->assertEquals('bar', $barRoute->getActionName());
    }

    public function testMiddlewaresConfiguration()
    {
        $middlewares = new MiddlewaresCollection(['mid1', 'mid2']);
        $router = new Router($middlewares);
        $router->bind('foo', 'FooController@foo');
        $this->assertEquals(['mid1', 'mid2'], $router->resolve('foo')->getMiddlewaresCollection()->getMiddlewares());
    }

    public function testGroupMiddlewaresConfiguration()
    {
        $middlewares = new MiddlewaresCollection(['mid1']);
        $router = new Router($middlewares);
        $router->bind('foo', 'FooController@foo');
        $router->group(
            function (MiddlewaresCollection $middlewaresCollection) {
                $middlewaresCollection->addMiddleware('mid2');
            },
            function (Router $router) {
                $router->bind('bar', 'BarController@bar');
            }
        );
        $this->assertEquals(['mid1'], $router->resolve('foo')->getMiddlewaresCollection()->getMiddlewares());
        $this->assertEquals(['mid1', 'mid2'], $router->resolve('bar')->getMiddlewaresCollection()->getMiddlewares());
    }

    public function testGroupMiddlewaresConfigurationWithArrayMiddlewares()
    {
        $middlewares = new MiddlewaresCollection(['mid1']);
        $router = new Router($middlewares);
        $router->bind('foo', 'FooController@foo');
        $router->group(
            ['mid2', 'mid3'],
            function (Router $router) {
                $router->bind('bar', 'BarController@bar');
            }
        );
        $this->assertEquals(['mid1'], $router->resolve('foo')->getMiddlewaresCollection()->getMiddlewares());
        $this->assertEquals(['mid1', 'mid2', 'mid3'], $router->resolve('bar')->getMiddlewaresCollection()->getMiddlewares());
    }

    public function testGroupMiddlewaresConfigurationWithStringMiddleware()
    {
        $middlewares = new MiddlewaresCollection(['mid1']);
        $router = new Router($middlewares);
        $router->bind('foo', 'FooController@foo');
        $router->group(
            'mid2',
            function (Router $router) {
                $router->bind('bar', 'BarController@bar');
            }
        );
        $this->assertEquals(['mid1'], $router->resolve('foo')->getMiddlewaresCollection()->getMiddlewares());
        $this->assertEquals(['mid1', 'mid2'], $router->resolve('bar')->getMiddlewaresCollection()->getMiddlewares());
    }
}
