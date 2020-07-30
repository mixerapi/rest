<?php

namespace MixerApi\Rest\Test\TestCase\Lib\Route;

use Cake\TestSuite\TestCase;
use MixerApi\Rest\Lib\Controller\ReflectedControllerDecorator;
use MixerApi\Rest\Lib\Exception\RunTimeException;
use MixerApi\Rest\Lib\Route\RouteWriter;

class RouteWriterTest extends TestCase
{
    public function testConstructException()
    {
        $this->expectException(RunTimeException::class);

        $decorator = new ReflectedControllerDecorator(
            'MixerApi\Rest\Test\App\Controller\ActorsController',
            'MixerApi\Rest\Test\App'
        );

        new RouteWriter([$decorator], '/nope/nope/and/nope');
    }
}