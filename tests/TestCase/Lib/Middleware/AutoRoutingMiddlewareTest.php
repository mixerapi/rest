<?php

namespace MixerApi\Rest\Test\TestCase\Lib\Middleware;

use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use MixerApi\Rest\Lib\Middleware\AutoRoutingMiddleware;
use MixerApi\Rest\Test\App\Http\TestRequestHandler;
use Psr\Http\Message\ResponseInterface;

class AutoRoutingMiddlewareTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Router::resetRoutes();
    }

    /**
     * Mimics AutoRoutingMiddleware with no arguments
     *
     * @throws \ReflectionException
     */
    public function testProcess()
    {
        $auto = new AutoRoutingMiddleware(['namespace' => 'MixerApi\Rest\Test\App\Controller', '/']);
        $this->assertInstanceOf(
            ResponseInterface::class,
            $auto->process(new ServerRequest(), new TestRequestHandler())
        );

        $collection = Router::getRouteCollection();
        $reflection = new \ReflectionClass($collection);
        $property = $reflection->getProperty('_routeTable');
        $property->setAccessible(true);
        $routeTable = $property->getValue($collection);

        $this->assertArrayHasKey('actors:index', $routeTable);
        $this->assertArrayHasKey('countries:cities:index', $routeTable);
        $this->assertArrayHasKey('sub:languages:index', $routeTable);
    }

    /**
     * Sub controller only test
     *
     * @throws \ReflectionException
     */
    public function testProcessSubController()
    {
        $auto = new AutoRoutingMiddleware(['namespace' => 'MixerApi\Rest\Test\App\Controller\Countries', '/']);
        $this->assertInstanceOf(
            ResponseInterface::class,
            $auto->process(new ServerRequest(), new TestRequestHandler())
        );

        $collection = Router::getRouteCollection();
        $reflection = new \ReflectionClass($collection);
        $property = $reflection->getProperty('_routeTable');
        $property->setAccessible(true);
        $routeTable = $property->getValue($collection);

        $this->assertArrayHasKey('cities:index', $routeTable);
        $this->assertCount(5, $routeTable);
    }

    public function testProcessPlugin()
    {
        $this->markTestIncomplete();
    }
}