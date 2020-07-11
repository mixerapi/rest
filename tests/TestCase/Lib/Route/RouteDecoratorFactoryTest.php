<?php

namespace MixerApiRest\Test\TestCase\Lib\Route;

use Cake\TestSuite\TestCase;
use MixerApiRest\Lib\Controller\ReflectedControllerDecorator;
use MixerApiRest\Lib\Route\RouteDecorator;
use MixerApiRest\Lib\Route\RouteDecoratorFactory;

class RouteDecoratorFactoryTest extends TestCase
{
    public function testCreateFromReflectedControllerDecorator()
    {
        $reflectedControllerDecorator = new ReflectedControllerDecorator(
            'MixerApiRest\Test\App\Controller\ActorsController',
            'MixerApiRest\Test\App'
        );

        $routeDecorators = (new RouteDecoratorFactory(''))
            ->createFromReflectedControllerDecorator($reflectedControllerDecorator);

        $this->assertIsArray($routeDecorators);
        $this->assertInstanceOf(RouteDecorator::class,reset($routeDecorators));
    }

    public function testCreateFromReflectedControllerDecoratorWithPlugin()
    {
        $this->markTestIncomplete();
    }
}