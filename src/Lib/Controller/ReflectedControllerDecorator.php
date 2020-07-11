<?php
declare(strict_types=1);

namespace MixerApiRest\Lib\Controller;

use MixerApiRest\Lib\Exception\InvalidControllerException;
use MixerApiRest\Lib\Exception\RunTimeException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Class ReflectedControllerDecorator
 *
 * Decorates an instance of a CakePHP Controller as a ReflectedClass
 *
 * @package MixerApiRest\Lib\Controller
 */
class ReflectedControllerDecorator
{
    /**
     * @var \ReflectionClass
     */
    private $reflectedController;

    /**
     * Can be instantiated with a fully qualified namespace or ReflectionClass instance
     *
     * @param mixed $controller FQN or ReflectionClass
     * @param string $namespace Namespace of the controllers
     * @throws \ReflectionException
     * @throws \MixerApiRest\Lib\Exception\RunTimeException
     * @throws \MixerApiRest\Lib\Exception\InvalidControllerException
     */
    public function __construct($controller, string $namespace)
    {
        if (is_string($controller)) {
            try {
                $this->reflectedController = new ReflectionClass($controller);
            } catch (ReflectionException $e) {
                throw new RunTimeException("Unable to create ReflectionClass using `$controller`");
            }
        } elseif ($controller instanceof ReflectionClass) {
            $this->reflectedController = $controller;
        }

        if (!$this->reflectedController->isSubclassOf($namespace . '\Controller\AppController')) {
            throw new InvalidControllerException(
                sprintf(
                    'Controller `%s` must be a subclass of AppController',
                    $this->getReflectedController()->getShortName()
                )
            );
        }
    }

    /**
     * Gets a Controllers methods as an array of ReflectionMethod instances
     *
     * @return \ReflectionMethod[]
     * @throws \MixerApiRest\Lib\Exception\RunTimeException
     */
    public function getMethods(): array
    {
        try {
            return array_filter(
                $this->reflectedController->getMethods(ReflectionMethod::IS_PUBLIC),
                function ($method) {
                    return $method->class == $this->reflectedController->getName();
                }
            );
        } catch (\Exception $e) {
            throw new RunTimeException($e->getMessage());
        }
    }

    /**
     * @return \ReflectionClass
     */
    public function getReflectedController(): ReflectionClass
    {
        return $this->reflectedController;
    }

    /**
     * Returns the CakePHP controller as a resource name, suitable for route resources
     *
     * @return string
     */
    public function getResourceName(): string
    {
        $shortName = $this->getReflectedController()->getShortName();

        return str_replace('Controller', '', $shortName);
    }
}
