<?php

namespace MixerApi\Rest\Test\TestCase\Lib\Route;

use Cake\Routing\Route\Route;
use Cake\TestSuite\TestCase;
use MixerApi\Rest\Lib\Exception\RestfulRouteException;
use MixerApi\Rest\Lib\Route\RouteDecorator;
use MixerApi\Rest\Lib\Route\RouteFactory;

class RouteDecoratorTest extends TestCase
{
    public function testConstruct()
    {
        $controller = 'Actors';
        $action = 'index';

        $decorator = new RouteDecorator(
            RouteFactory::create('actors/index', $controller, $action)
        );

        $this->assertInstanceOf(RouteDecorator::class, $decorator);

        $this->assertEquals('actors:index', $decorator->getName());
        $this->assertEquals(['GET'], $decorator->getMethods());
        $this->assertEquals($action, $decorator->getAction());
        $this->assertEquals('actors/index', $decorator->getTemplate());
        $this->assertEquals(null, $decorator->getPlugin());
        $this->assertInstanceOf(Route::class, $decorator->getRoute());
    }

    public function testConstructException()
    {
        $this->expectException(RestfulRouteException::class);

        $controller = 'Actors';
        $action = 'index';

        new RouteDecorator(
            RouteFactory::create('actors/index', $controller, $action)->setMethods([])
        );
    }
}