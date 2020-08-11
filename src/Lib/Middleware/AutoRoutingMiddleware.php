<?php
declare(strict_types=1);

namespace MixerApi\Rest\Lib\Middleware;

use Cake\Core\Configure;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use MixerApi\Rest\Lib\Route\ResourceScanner;
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
        $this->namespace = $options['namespace'] ?? Configure::read('App.namespace') . '\Controller';
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
        $resources = (new ResourceScanner($this->namespace))->getControllerDecorators();

        foreach ($resources as $resource) {
            if (!$resource->hasCrud()) {
                continue;
            }

            $paths = $resource->getPaths($this->namespace);

            if (empty($paths)) {
                $builder->resources($resource->getResourceName());
                continue;
            }

            $builder->resources($resource->getResourceName(), [
                'path' => $resource->getPathTemplate($this->namespace),
                'prefix' => end($paths),
            ]);
        }

        return $handler->handle($request);
    }
}
