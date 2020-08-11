<?php

namespace MixerApi\Rest\Lib\Route;

use Cake\Core\Configure;
use MixerApi\Rest\Lib\Controller\ControllerUtility;

class ResourceScanner
{
    /**
     * @var string|null $namespace
     */
    private $namespace;

    /**
     * @param string|null $namespace
     */
    public function __construct(?string $namespace = null)
    {
        $this->namespace = $namespace ?? Configure::read('App.namespace') . '\Controller';
    }

    /**
     * Returns an array of ReflectedControllerDecorator that can be RESTful resources
     *
     * @return \MixerApi\Rest\Lib\Controller\ReflectedControllerDecorator[]
     * @throws \ReflectionException
     */
    public function getControllerDecorators(): array
    {
        $controllers = ControllerUtility::getControllersFqn($this->namespace);
        $controllerDecorators = ControllerUtility::getReflectedControllerDecorators($controllers);

        return array_values(array_filter($controllerDecorators, function($controller) {
            return $controller->hasCrud();
        }));
    }
}