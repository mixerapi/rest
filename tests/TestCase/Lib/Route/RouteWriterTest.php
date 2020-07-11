<?php

namespace MixerApiRest\Test\TestCase\Lib\Route;

use Cake\TestSuite\TestCase;
use MixerApiRest\Lib\Controller\ReflectedControllerDecorator;
use MixerApiRest\Lib\Exception\RunTimeException;
use MixerApiRest\Lib\Route\RouteWriter;

class RouteWriterTest extends TestCase
{
    public function testConstructException()
    {
        $this->expectException(RunTimeException::class);

        $decorator = new ReflectedControllerDecorator(
            'MixerApiRest\Test\App\Controller\ActorsController',
            'MixerApiRest\Test\App'
        );

        new RouteWriter([$decorator], '/nope/nope/and/nope');
    }
}