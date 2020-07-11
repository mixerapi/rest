<?php

namespace MixerApiRest\Test\TestCase\Command;

use Cake\Routing\Route\Route;
use Cake\Routing\Router;
use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

class ListRoutesCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    public $fixtures = [
        'plugin.MixerApiRest.Actors'
    ];

    public function setUp() : void
    {
        parent::setUp();
        $this->setAppNamespace('MixerApiRest\Test\App');
        $this->useCommandRunner();
    }

    public function testExecute()
    {
        $this->exec('mixerapi:rest list');
        $this->assertOutputContains('actors:index', 'route name');
        $this->assertOutputContains('actors', 'uri template');
        $this->assertOutputContains('GET', 'method(s)');
        $this->assertOutputContains('Actors', 'controller');
    }

    public function testExecuteRoutesNotFound()
    {
        $this->exec('mixerapi:rest list --reloadRoutes');
        $this->assertExitError();
    }
}