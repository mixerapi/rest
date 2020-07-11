<?php

namespace MixerApiRest\Test\TestCase\Lib\Controller;

use Cake\TestSuite\TestCase;
use MixerApiRest\Lib\Controller\ReflectedControllerDecorator;
use MixerApiRest\Lib\Exception\RunTimeException;

class ReflectedControllerDecoratorTest extends TestCase
{
    public function testConstruct()
    {
        $decorator = new ReflectedControllerDecorator(
            'MixerApiRest\Test\App\Controller\ActorsController',
            'MixerApiRest\Test\App'
        );

        $this->assertInstanceOf(ReflectedControllerDecorator::class, $decorator);

        $decorator = new ReflectedControllerDecorator(
            new \ReflectionClass('MixerApiRest\Test\App\Controller\ActorsController'),
            'MixerApiRest\Test\App'
        );

        $this->assertInstanceOf(ReflectedControllerDecorator::class, $decorator);
    }

    public function testConstructException()
    {
        $this->expectException(RunTimeException::class);
        $decorator = new ReflectedControllerDecorator(
            'MixerApiRest\Test\Nope',
            'MixerApiRest\Test\Nope'
        );
    }
}