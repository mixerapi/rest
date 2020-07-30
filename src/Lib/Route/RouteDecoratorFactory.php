<?php
declare(strict_types=1);

namespace MixerApi\Rest\Lib\Route;

use Cake\Utility\Text;
use MixerApi\Rest\Lib\Controller\ReflectedControllerDecorator;
use MixerApi\Rest\Lib\Exception\RestfulRouteException;

class RouteDecoratorFactory
{
    /**
     * @var string
     */
    private $plugin;

    /**
     * @param string $plugin Plugin name
     */
    public function __construct(string $plugin = '')
    {
        $this->plugin = trim($plugin);
    }

    /**
     * Creates a RouteDecorator instance from a ReflectedControllerDecorator instance
     *
     * @param \MixerApi\Rest\Lib\Controller\ReflectedControllerDecorator $decorator ReflectedControllerDecorator
     * @return \MixerApi\Rest\Lib\Route\RouteDecorator[]
     */
    public function createFromReflectedControllerDecorator(ReflectedControllerDecorator $decorator): array
    {
        $routeDecorators = [];

        $controller = $decorator->getResourceName();

        $template = '';

        if (!empty($this->plugin)) {
            $template = Text::slug($this->plugin);
        }

        $template .= Text::slug(strtolower($controller));

        foreach ($decorator->getMethods() as $action) {
            $uriTemplate = $template;

            if (in_array($action->getName(), ['add','view','update','delete'])) {
                $uriTemplate .= '/:id';
            }

            try {
                $routeDecorators[] = new RouteDecorator(
                    RouteFactory::create(trim($uriTemplate), $controller, $action->getName(), $this->plugin)
                );
            } catch (RestfulRouteException $e) {
            }
        }

        return $routeDecorators;
    }
}
