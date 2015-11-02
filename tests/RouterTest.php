<?php

use Upgate\LaravelJsonRpc\Server\Router;

class RouterTest extends PHPUnit_Framework_TestCase
{

    public function testMethod()
    {
        $router = new Router();
        $router->bind('foo.bar', 'foo@bar');
    }

}
