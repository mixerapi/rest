<?php

namespace MixerApi\Rest\Test\TestCase\Command;

use Cake\Routing\Route\Route;
use Cake\Routing\Router;
use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

class CreateRoutesCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    public $fixtures = [
        'plugin.MixerApi/Rest.Actors'
    ];

    private const ROUTE_FILE = 'routes_test.php';
    private const ROUTE_BASE = 'routes_base.php';

    public function setUp() : void
    {
        parent::setUp();
        $this->setAppNamespace('MixerApi\Rest\Test\App');
        $this->useCommandRunner();
        touch(CONFIG . self::ROUTE_FILE);
    }

    public function testSuccess()
    {
        $file = self::ROUTE_FILE;
        $this->exec("mixerapi:rest route create --routesFile $file", ['Y']);
        $this->assertOutputContains('Routes were written to ' . CONFIG . $file);
        $this->assertExitSuccess();
    }

    public function testAbort()
    {
        $file = self::ROUTE_FILE;
        $this->exec("mixerapi:rest route create --routesFile $file", ['N']);
        $this->assertExitError();
    }

    public function testNoControllersExitError()
    {
        $file = self::ROUTE_FILE;
        $this->exec("mixerapi:rest route create --routesFile $file --plugin Nope", ['Y']);
        $this->assertExitError();
    }

    public function testPluginSuccess()
    {
        $this->markTestIncomplete();
    }

    public function testDisplaySuccess()
    {
        $this->exec("mixerapi:rest route create --display");
        $this->assertOutputContains('actors:index', 'route name');
        $this->assertOutputContains('actors', 'uri template');
        $this->assertOutputContains('GET', 'method(s)');
        $this->assertOutputContains('Actors', 'controller');
        $this->assertExitSuccess();
    }
}