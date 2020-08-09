<?php
declare(strict_types=1);

namespace MixerApi\Rest\Lib\Controller;

use Cake\Controller\Controller;
use MixerApi\Rest\Lib\Exception\InvalidControllerException;
use MixerApi\Rest\Lib\Exception\RunTimeException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Class ReflectedControllerDecorator
 *
 * Decorates an instance of a CakePHP Controller as a ReflectedClass
 *
 * @package MixerApi\Rest\Lib\Controller
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
     * @throws \ReflectionException
     * @throws \MixerApi\Rest\Lib\Exception\RunTimeException
     * @throws \MixerApi\Rest\Lib\Exception\InvalidControllerException
     */
    public function __construct($controller)
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

        if (!$this->reflectedController->isSubclassOf(Controller::class)) {
            throw new InvalidControllerException(
                sprintf(
                    'Controller `%s` must be a subclass of AppController',
                    $this->getReflectedController()->getShortName()
                )
            );
        }
    }

    /**
     * Returns an array of namespaces for the controller, relative to the $baseNamespace argument.
     *
     * @param string $baseNamespace the base namespace (e.g. App\Controller)
     * @return array
     */
    public function getPaths(string $baseNamespace): array
    {
        $namespace = $this->getReflectedController()->getName();
        $relativeNs = str_replace($baseNamespace . '\\', '', $namespace);

        $paths = explode('\\', $relativeNs);

        if (empty($paths)) {
            return [];
        }

        array_pop($paths);

        if (empty($paths)) {
            return [];
        }

        return $paths;
    }

    /**
     * Does the controller have a CRUD method: index, view, add, update, and delete
     *
     * @return bool
     */
    public function hasCrud(): bool
    {
        $crud = ['index','view','add','update','delete'];

        foreach ($this->getMethods() as $method) {
            if (in_array($method->getName(), $crud)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets a Controllers methods as an array of ReflectionMethod instances
     *
     * @return \ReflectionMethod[]
     * @throws \MixerApi\Rest\Lib\Exception\RunTimeException
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
