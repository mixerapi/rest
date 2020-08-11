<?php
declare(strict_types=1);

namespace MixerApi\Rest\Lib\Controller;

use Cake\Core\Configure;
use MixerApi\Rest\Lib\Exception\InvalidControllerException;
use TheCodingMachine\ClassExplorer\Glob\GlobClassExplorer;
use Symfony\Component\Cache\Simple\NullCache;
use Mouf\Composer\ClassNameMapper;

/**
 * Class ControllerUtility
 *
 * Utilities for working with CakePHP controllers
 *
 * @package MixerApi\Rest\Lib\Controller
 */
class ControllerUtility
{
    /**
     * Gets array of controllers fully qualified namespace as strings for the given namespace, defaults to
     * APP.namespace if no argument is given
     *
     * @param string|null $namespace Fully qualified namespace
     * @return string[]
     * @throws \Exception
     */
    public static function getControllersFqn(?string $namespace): array
    {
        $namespace = $namespace ?? Configure::read('App.namespace') . '\Controller';

        $classNameMapper = ClassNameMapper::createFromComposerFile(null,null,true);
        $explorer = new GlobClassExplorer($namespace, new NullCache(), 0, $classNameMapper);
        return array_keys($explorer->getClassMap());
    }

    /**
     * Gets an array of ReflectedControllerDecorators
     *
     * @param string[] $controllers an array of controllers as fully qualified name space strings
     * @return \MixerApi\Rest\Lib\Controller\ReflectedControllerDecorator[]
     * @throws \ReflectionException
     */
    public static function getReflectedControllerDecorators(array $controllers): array
    {
        $decoratedControllers = [];

        foreach ($controllers as $controllerFqn) {
            try {
                $decoratedControllers[] = new ReflectedControllerDecorator($controllerFqn);
            } catch (InvalidControllerException $e) {
                // maybe do something here?
            }
        }

        return $decoratedControllers;
    }
}
