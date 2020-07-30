<?php
declare(strict_types=1);

namespace MixerApi\Rest\Lib\Route;

use MixerApi\Rest\Lib\Exception\RunTimeException;

/**
 * Class RouteWriter
 *
 * Writes routes to `config/routes.php`
 *
 * @package MixerApi\Rest\Lib\Route
 */
class RouteWriter
{
    /**
     * @var \MixerApi\Rest\Lib\Controller\ReflectedControllerDecorator[]
     */
    private $decorators;

    /**
     * @var string
     */
    private $configDir;

    /**
     * @param \MixerApi\Rest\Lib\Controller\ReflectedControllerDecorator[] $decorators Array of Decorator instances
     * @param string $configDir An absolute directory path to userland CakePHP config
     */
    public function __construct(array $decorators, string $configDir)
    {
        if (!is_dir($configDir)) {
            throw new RunTimeException("Directory does not exist `$configDir`");
        }

        $this->decorators = $decorators;
        $this->configDir = $configDir;
    }

    /**
     * Overwrites userland `routes.php` file
     *
     * @param string $file Filename, defaults to routes.php
     * @return void
     */
    public function overwrite(string $file = 'routes.php'): void
    {
        $routesFile = $this->configDir . $file;

        if (!is_file($routesFile)) {
            throw new RunTimeException('Routes file does not exist `' . $routesFile . '`');
        }

        if (!is_writable($routesFile)) {
            throw new RunTimeException('Routes file is not writable `' . $routesFile . '`');
        }

        if (!copy($this->getTemplateFilePath(), $routesFile)) {
            throw new RunTimeException('Error copying routes to `' . $routesFile . '`');
        }

        $fileResource = fopen($routesFile, 'a');

        fwrite($fileResource, $this->getRoutesPhpCode());
        fclose($fileResource);
    }

    /**
     * Gets the route as a string of PHP code
     *
     * @return string
     */
    private function getRoutesPhpCode(): string
    {
        $routes = '';
        $routes .= "\$routes->scope('/', function (RouteBuilder \$builder) {\r";
        $routes .= "\t\$builder->fallbacks();\r";
        $routes .= "\t\$builder->setExtensions(['json']);\r";

        foreach ($this->decorators as $decorator) {
            $routes .= sprintf("\t\$builder->resources('%s');\r", $decorator->getResourceName());
        }

        $routes .= "});\r";

        return $routes;
    }

    /**
     * @return string
     */
    private function getTemplateFilePath(): string
    {
        return __DIR__ . DS . '..' . DS . 'assets' . DS . 'routes.php';
    }
}
