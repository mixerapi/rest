<?php
declare(strict_types=1);

namespace MixerApiRest\Lib\Controller;

use Cake\Core\Configure;
use HaydenPierce\ClassFinder\ClassFinder;
use MixerApiRest\Lib\Exception\InvalidControllerException;

/**
 * Class ControllerUtility
 *
 * Utilities for working with CakePHP controllers
 *
 * @package MixerApiRest\Lib\Controller
 */
class ControllerUtility
{
    /**
     * Gets array of controllers fully qualified namespace as strings for the given namespace, defaults to
     * APP.namespace if no argument is given
     *
     * @param string $namespace Fully qualified namespace
     * @return string[]
     * @throws \Exception
     */
    public static function getControllersFqn(?string $namespace): array
    {
        $namespace = $namespace ?? Configure::read('App.namespace');

        return ClassFinder::getClassesInNamespace("$namespace\Controller");
    }

    /**
     * Gets an array of ReflectedControllerDecorators
     *
     * @param string[] $controllers An array of controllers as fully qualified name space strings
     * @param string $namespace Fqn
     * @return \MixerApiRest\Lib\Controller\ReflectedControllerDecorator[]
     * @throws \ReflectionException
     */
    public static function getReflectedControllerDecorators(array $controllers, string $namespace): array
    {
        $decoratedControllers = [];

        foreach ($controllers as $controllerFqn) {
            try {
                $decoratedControllers[] = new ReflectedControllerDecorator($controllerFqn, $namespace);
            } catch (InvalidControllerException $e) {
                // maybe do something here?
            }
        }

        return $decoratedControllers;
    }
}
