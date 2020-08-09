<?php
declare(strict_types=1);

namespace MixerApi\Rest\Lib\Middleware;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use MixerApi\Rest\Lib\Controller\ControllerUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AutoRoutingMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param array $options options for middleware
     */
    public function __construct(array $options = [])
    {
        $this->namespace = $options['namespace'] ?? 'App\Controller';
        $this->prefix = $options['prefix'] ?? '/';
    }

    /**
     * Apply routing and update the request.
     *
     * Any route/path specific middleware will be wrapped around $next and then the new middleware stack will be
     * invoked.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The request handler.
     * @return \Psr\Http\Message\ResponseInterface A response.
     * @throws \ReflectionException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $collection = Router::getRouteCollection();

        $builder = new RouteBuilder($collection, $this->prefix);
        $controllers = ControllerUtility::getControllersFqn($this->namespace);
        sort($controllers);

        $controllerDecorators = ControllerUtility::getReflectedControllerDecorators($controllers);

        foreach ($controllerDecorators as $controller) {
            if (!$controller->hasCrud()) {
                continue;
            }

            $nsPaths = $controller->getPaths($this->namespace);

            if (empty($nsPaths)) {
                $builder->resources($controller->getResourceName());
                continue;
            }

            $paths = array_map(function ($path) {
                return Inflector::dasherize($path);
            }, $nsPaths);

            $path = implode('/', $paths) . '/' . Inflector::dasherize($controller->getResourceName());
            $prefix = implode('/', $nsPaths);

            $builder->resources($controller->getResourceName(), [
                'path' => $path,
                'prefix' => $prefix,
            ]);
        }

        return $handler->handle($request);
    }
}
