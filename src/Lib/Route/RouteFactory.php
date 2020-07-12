<?php
declare(strict_types=1);

namespace MixerApiRest\Lib\Route;

use Cake\Routing\Route\Route;
use MixerApiRest\Lib\Exception\RestfulRouteException;

class RouteFactory
{
    private const ACTION_HTTP_METHODS = [
        'index' => 'GET',
        'add' => 'POST',
        'view' => 'GET',
        'edit' => ['PATCH','PUT'],
        'delete' => 'DELETE',
    ];

    /**
     * Creates an instance of a CakePHP Route
     *
     * @param string $template URI Template
     * @param string $controller Controller name
     * @param string $action Action method
     * @param string|null $plugin Plugin name
     * @return \Cake\Routing\Route\Route
     * @throws \MixerApiRest\Lib\Exception\RestfulRouteException
     */
    public static function create(string $template, string $controller, string $action, ?string $plugin = null): Route
    {
        if (!isset(self::ACTION_HTTP_METHODS[$action])) {
            throw new RestfulRouteException("Action `$action` is unknown. This route will not be created");
        }

        return new Route($template, [
            '_method' => self::ACTION_HTTP_METHODS[$action],
            'action' => $action,
            'controller' => $controller,
            'plugin' => empty($plugin) ? null : $plugin,
        ]);
    }
}
