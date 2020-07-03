<?php

namespace RestBaker\Test\TestCase;

use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\Command\Command;

class RestBakerTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    public $fixtures = [
        'plugin.RestBaker.Departments',
        'plugin.RestBaker.Employees',
        'plugin.RestBaker.DepartmentEmployees',
    ];

    /** @var string  */
    private $controllers;

    public function setUp() : void
    {
        parent::setUp();
        $this->setAppNamespace('RestBaker\Test\App');
        $this->useCommandRunner();

        $this->controllers = APP . 'Controller' . DS;

        foreach (scandir($this->controllers) as $file) {
            if (!is_file($this->controllers . $file)) {
                continue;
            }
            unlink($this->controllers . $file);
        }
    }

    public function testBakeController()
    {
        $this->exec('bake controller Departments --no-test --theme RestBaker');

        $controllerFile = 'DepartmentsController.php';
        $assets = TEST . DS . 'assets' . DS;

        $this->assertOutputContains('Baking controller class for Departments...');
        $this->assertOutputContains('<success>Wrote</success>');
        $this->assertOutputContains('tests/test_app/App/Controller/' . $controllerFile);
        $this->assertFileExists($this->controllers . $controllerFile);
        $this->assertFileEquals($assets . $controllerFile, $this->controllers . $controllerFile);
    }
}