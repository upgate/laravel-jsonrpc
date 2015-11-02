<?php

use Illuminate\Contracts\Container\Container;
use Upgate\LaravelJsonRpc\Contract\Route;
use Upgate\LaravelJsonRpc\Exception\InvalidParamsException;
use Upgate\LaravelJsonRpc\Server\RequestParams;
use Upgate\LaravelJsonRpc\Server\RouteDispatcher;

class RouteDispatcherTest extends PHPUnit_Framework_TestCase
{

    public function testWithNoArguments()
    {
        $route = $this->getMockBuilder(Route::class)->getMock();
        $route->method('getControllerClass')->willReturn(RouteDispatcherTest_StubController::class);
        $route->method('getActionName')->willReturn('getArgumentsCount');
        /** @var Route $route */

        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->method('make')->with(RouteDispatcherTest_StubController::class)
            ->willReturn(new RouteDispatcherTest_StubController());
        /** @var Container $container */

        $routeDispatcher = new RouteDispatcher($container);
        $result = $routeDispatcher->dispatch($route, null);
        $this->assertEquals(0, $result, 'Failed with null request params');
        $result = $routeDispatcher->dispatch($route, RequestParams::constructPositional([]));
        $this->assertEquals(0, $result, 'Failed with empty positional params');
        $result = $routeDispatcher->dispatch($route, RequestParams::constructNamed([]));
        $this->assertEquals(0, $result, 'Failed with empty named params');
    }

    public function testWithRequiredPositionalParams()
    {
        $route = $this->getMockBuilder(Route::class)->getMock();
        $route->method('getControllerClass')->willReturn(RouteDispatcherTest_StubController::class);
        $route->method('getActionName')->willReturn('getRequiredArguments');
        /** @var Route $route */

        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->method('make')->with(RouteDispatcherTest_StubController::class)
            ->willReturn(new RouteDispatcherTest_StubController());
        /** @var Container $container */

        $routeDispatcher = new RouteDispatcher($container);

        $result = $routeDispatcher->dispatch(
            $route,
            RequestParams::constructPositional(
                ['foo_value', 'bar_value', 42]
            )
        );
        $this->assertEquals(
            ['foo' => 'foo_value', 'bar' => 'bar_value', 'baz' => 42],
            $result,
            'Failed with all required params were provided'
        );

        $this->setExpectedException(InvalidParamsException::class, '"bar" is required', -32602);
        $routeDispatcher->dispatch(
            $route,
            RequestParams::constructPositional([1])
        );
    }

    public function testWithRequiredNamedParams()
    {
        $route = $this->getMockBuilder(Route::class)->getMock();
        $route->method('getControllerClass')->willReturn(RouteDispatcherTest_StubController::class);
        $route->method('getActionName')->willReturn('getRequiredArguments');
        /** @var Route $route */

        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->method('make')->with(RouteDispatcherTest_StubController::class)
            ->willReturn(new RouteDispatcherTest_StubController());
        /** @var Container $container */

        $routeDispatcher = new RouteDispatcher($container);

        $result = $routeDispatcher->dispatch(
            $route,
            RequestParams::constructNamed(
                ['baz' => 42, 'foo' => 'foo_value', 'bar' => 'bar_value']
            )
        );
        $this->assertEquals(
            ['foo' => 'foo_value', 'bar' => 'bar_value', 'baz' => 42],
            $result,
            'Failed with all required params were provided'
        );

        $this->setExpectedException(InvalidParamsException::class, '"bar" is required', -32602);
        $routeDispatcher->dispatch(
            $route,
            RequestParams::constructPositional([1])
        );
    }

    public function testWithOptionalPositionalParams()
    {
        $route = $this->getMockBuilder(Route::class)->getMock();
        $route->method('getControllerClass')->willReturn(RouteDispatcherTest_StubController::class);
        $route->method('getActionName')->willReturn('getArgumentsWithLastOptional');
        /** @var Route $route */

        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->method('make')->with(RouteDispatcherTest_StubController::class)
            ->willReturn(new RouteDispatcherTest_StubController());
        /** @var Container $container */

        $routeDispatcher = new RouteDispatcher($container);

        $result = $routeDispatcher->dispatch($route, RequestParams::constructPositional([1, 42]));
        $this->assertEquals(['required' => 1, 'optional' => 42], $result, 'Failed with provided optional value');

        $result = $routeDispatcher->dispatch($route, RequestParams::constructPositional([2]));
        $this->assertEquals(['required' => 2, 'optional' => 'default'], $result, 'Failed with missing optional value');

        $this->setExpectedException(InvalidParamsException::class, '"required" is required', -32602);
        $routeDispatcher->dispatch($route, null);
    }

    public function testWithOptionalNamedParams()
    {
        $route = $this->getMockBuilder(Route::class)->getMock();
        $route->method('getControllerClass')->willReturn(RouteDispatcherTest_StubController::class);
        $route->method('getActionName')->willReturn('getArgumentsWithLastOptional');
        /** @var Route $route */

        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->method('make')->with(RouteDispatcherTest_StubController::class)
            ->willReturn(new RouteDispatcherTest_StubController());
        /** @var Container $container */

        $routeDispatcher = new RouteDispatcher($container);

        $result = $routeDispatcher->dispatch($route, RequestParams::constructNamed(['required' => 1, 'optional' => 42]));
        $this->assertEquals(['required' => 1, 'optional' => 42], $result, 'Failed with provided optional value');

        $result = $routeDispatcher->dispatch($route, RequestParams::constructNamed(['required' => 2]));
        $this->assertEquals(['required' => 2, 'optional' => 'default'], $result, 'Failed with missing optional value');

        $this->setExpectedException(InvalidParamsException::class, '"required" is required', -32602);
        $routeDispatcher->dispatch($route, null);
    }

    public function testDependencyInjection()
    {
        $route = $this->getMockBuilder(Route::class)->getMock();
        $route->method('getControllerClass')->willReturn(RouteDispatcherTest_StubController::class);
        $route->method('getActionName')->willReturn('dependencies');
        /** @var Route $route */

        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->method('make')->will(
            $this->returnCallback(
                function ($className) {
                    return new $className;
                }
            )
        );
        /** @var Container $container */

        $routeDispatcher = new RouteDispatcher($container);
        $this->assertTrue($routeDispatcher->dispatch($route, null));
    }

    public function testDependencyInjectionWithPositionalParameters()
    {
        $route = $this->getMockBuilder(Route::class)->getMock();
        $route->method('getControllerClass')->willReturn(RouteDispatcherTest_StubController::class);
        $route->method('getActionName')->willReturn('dependenciesWithArgs');
        /** @var Route $route */

        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->method('make')->will(
            $this->returnCallback(
                function ($className) {
                    return new $className;
                }
            )
        );
        /** @var Container $container */

        $routeDispatcher = new RouteDispatcher($container);

        $result = $routeDispatcher->dispatch($route, RequestParams::constructPositional([1, 42]));
        $this->assertEquals(['required' => 1, 'optional' => 42], $result, 'Failed with provided optional value');

        $result = $routeDispatcher->dispatch($route, RequestParams::constructPositional([2]));
        $this->assertEquals(['required' => 2, 'optional' => 'default'], $result, 'Failed with missing optional value');

        $this->setExpectedException(InvalidParamsException::class, '"required" is required', -32602);
        $routeDispatcher->dispatch($route, null);
    }

    public function testDependencyInjectionWithNamedParameters()
    {
        $route = $this->getMockBuilder(Route::class)->getMock();
        $route->method('getControllerClass')->willReturn(RouteDispatcherTest_StubController::class);
        $route->method('getActionName')->willReturn('dependenciesWithArgs');
        /** @var Route $route */

        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->method('make')->will(
            $this->returnCallback(
                function ($className) {
                    return new $className;
                }
            )
        );
        /** @var Container $container */

        $routeDispatcher = new RouteDispatcher($container);

        $result = $routeDispatcher->dispatch(
            $route,
            RequestParams::constructNamed(['required' => 1, 'optional' => 42])
        );
        $this->assertEquals(['required' => 1, 'optional' => 42], $result, 'Failed with provided optional value');

        $result = $routeDispatcher->dispatch($route, RequestParams::constructNamed(['required' => 2]));
        $this->assertEquals(['required' => 2, 'optional' => 'default'], $result, 'Failed with missing optional value');

        $this->setExpectedException(InvalidParamsException::class, '"required" is required', -32602);
        $routeDispatcher->dispatch($route, null);
    }

    public function testRouteDispatcherControllerNamespace() {
        $route = $this->getMockBuilder(Route::class)->getMock();
        $route->method('getControllerClass')->willReturn('StubController');
        $route->method('getActionName')->willReturn('returnArg');
        /** @var Route $route */

        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->method('make')->will(
            $this->returnCallback(
                function ($className) {
                    return new $className;
                }
            )
        );
        /** @var Container $container */

        eval('
            namespace RouteDispatcherTestNs {
                class StubController {
                    public function returnArg($value) {
                        return $value;
                    }
                }
            }
        ');

        $routeDispatcher = new RouteDispatcher($container, 'RouteDispatcherTestNs');
        $result = $routeDispatcher->dispatch($route, RequestParams::constructPositional([100500]));
        $this->assertEquals(100500, $result);
    }

    public function testRouteDispatcherControllerNamespaceIsIgnoredForRouteControllerClassDefinitionStartingWithBackslash() {
        $route = $this->getMockBuilder(Route::class)->getMock();
        $route->method('getControllerClass')->willReturn('\RouteDispatcherTestNs\UseThisController');
        $route->method('getActionName')->willReturn('returnArg');
        /** @var Route $route */

        $container = $this->getMockBuilder(Container::class)->getMock();
        $container->method('make')->will(
            $this->returnCallback(
                function ($className) {
                    return new $className;
                }
            )
        );
        /** @var Container $container */

        eval('
            namespace RouteDispatcherTestNs {
                class UseThisController {
                    public function returnArg($value) {
                        return $value;
                    }
                }
            }
        ');

        $routeDispatcher = new RouteDispatcher($container, 'NoSuchNamespace');
        $result = $routeDispatcher->dispatch($route, RequestParams::constructPositional([100500]));
        $this->assertEquals(100500, $result);
    }

}

class RouteDispatcherTest_StubController
{

    public function getArgumentsCount()
    {
        return func_num_args();
    }

    public function getRequiredArguments($foo, $bar, $baz)
    {
        return ['foo' => $foo, 'bar' => $bar, 'baz' => $baz];
    }

    public function getArgumentsWithLastOptional($required, $optional = 'default')
    {
        return ['required' => $required, 'optional' => $optional];
    }

    public function dependencies(RouteDispatcherTest_Dependency1 $dep1, RouteDispatcherTest_Dependency2 $dep2)
    {
        return $dep1 && $dep2;
    }

    public function dependenciesWithArgs(
        RouteDispatcherTest_Dependency1 $dep1,
        RouteDispatcherTest_Dependency2 $dep2,
        $required,
        $optional = 'default'
    ) {
        $dep1 && $dep2;

        return ['required' => $required, 'optional' => $optional];
    }

}

class RouteDispatcherTest_Dependency1
{
}

class RouteDispatcherTest_Dependency2
{
}
