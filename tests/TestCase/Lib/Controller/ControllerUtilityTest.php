<?php

namespace MixerApiRest\Test\TestCase\Lib\Controller;

use Cake\TestSuite\TestCase;
use MixerApiRest\Lib\Controller\ControllerUtility;
use MixerApiRest\Lib\Exception\RunTimeException;

class ControllerUtilityTest extends TestCase
{
    public function testGetControllersFqn()
    {
        $this->assertIsArray(ControllerUtility::getControllersFqn('MixerApiRest\Test\App'));
    }
}