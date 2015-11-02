<?php

use Upgate\LaravelJsonRpc\Exception\RouteNotFoundException;
use Upgate\LaravelJsonRpc\Server\Router;

class RouterTest extends PHPUnit_Framework_TestCase
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
        $this->setExpectedException(RouteNotFoundException::class);
        $router->resolve('foo');
    }

}
