<?php

namespace MixerApi\Rest\Test\TestCase\Lib\Route;

use Cake\TestSuite\TestCase;
use MixerApi\Rest\Lib\Controller\ReflectedControllerDecorator;
use MixerApi\Rest\Lib\Exception\RunTimeException;
use MixerApi\Rest\Lib\Route\ResourceScanner;
use MixerApi\Rest\Lib\Route\RouteDecoratorFactory;
use MixerApi\Rest\Lib\Route\RouteWriter;

class RouteWriterTest extends TestCase
{
    private const ROUTE_FILE = 'routes_test.php';
    private const ROUTE_BASE = 'routes_base.php';

    public function setUp(): void
    {
        parent::setUp();
        copy(CONFIG . self::ROUTE_BASE, CONFIG . self::ROUTE_FILE);
    }

    public function testConstruct()
    {

        $resources = (new ResourceScanner('MixerApi\Rest\Test\App\Controller'))->getControllerDecorators();

        $rw = (new RouteWriter($resources,'MixerApi\Rest\Test\App\Controller', CONFIG, '/'));

        $this->assertEquals($resources, $rw->getResources());
        $this->assertEquals(CONFIG, $rw->getConfigDir());
        $this->assertEquals('/', $rw->getPrefix());
    }

    public function testConstructException()
    {
        $this->expectException(RunTimeException::class);

        $resources = (new ResourceScanner('MixerApi\Rest\Test\App\Controller'))->getControllerDecorators();

        new RouteWriter($resources, 'MixerApi\Rest\Test\App\Controller','/nope/and/nope', '/');
    }

    /**
     * Test merge on regular App\Controller with sub controllers
     */
    public function testMerge()
    {
        $resources = (new ResourceScanner('MixerApi\Rest\Test\App\Controller'))->getControllerDecorators();

        (new RouteWriter($resources,'MixerApi\Rest\Test\App\Controller', CONFIG, '/'))
            ->merge(self::ROUTE_FILE);

        $contents = file_get_contents(CONFIG . self::ROUTE_FILE);

        $this->assertTextContains("\$builder->resources('Actors')", $contents);
        $this->assertTextContains("\$builder->resources('Films')", $contents);
        $this->assertTextContains(
            "\$builder->resources('Cities', ['path' => 'countries/cities', 'prefix' => 'Countries']);",
            $contents
        );
        $this->assertTextContains(
            "\$builder->resources('Languages', ['path' => 'sub/languages', 'prefix' => 'Sub']);",
            $contents
        );
    }
}