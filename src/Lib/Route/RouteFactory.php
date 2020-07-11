<?php
declare(strict_types=1);

namespace MixerApiRest\Lib\Route;

use Cake\Routing\Route\Route;

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
     */
    public static function create(string $template, string $controller, string $action, ?string $plugin = null): Route
    {
        return new Route($template, [
            '_method' => self::ACTION_HTTP_METHODS[$action],
            'action' => $action,
            'controller' => $controller,
            'plugin' => empty($plugin) ? null : $plugin,
        ]);
    }
}
