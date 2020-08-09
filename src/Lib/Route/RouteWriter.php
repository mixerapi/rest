<?php
declare(strict_types=1);

namespace MixerApi\Rest\Lib\Route;

use MixerApi\Rest\Lib\Exception\RunTimeException;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use MixerApi\Rest\Lib\Parser\RouteScopeVisitor;
use PhpParser\PrettyPrinter\Standard;

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
     * @var \MixerApi\Rest\Lib\Route\RouteDecorator[]
     */
    private $routeDecorators;

    /**
     * @var string
     */
    private $configDir;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param \MixerApi\Rest\Lib\Route\RouteDecorator[] $routeDecorators Array of Decorator instances
     * @param string $configDir An absolute directory path to userland CakePHP config
     * @param string $prefix route prefix (e.g `/`)
     */
    public function __construct(array $routeDecorators, string $configDir, string $prefix)
    {
        if (!is_dir($configDir)) {
            throw new RunTimeException("Directory does not exist `$configDir`");
        }

        $this->routeDecorators = $routeDecorators;
        $this->configDir = $configDir;
        $this->prefix = $prefix;
    }

    /**
     * Merges routes into an existing scope in routes.php
     *
     * @param string $file
     */
    public function merge(string $file = 'routes.php'): void
    {
        $routesFile = $this->configDir . $file;

        if (!is_file($routesFile)) {
            throw new RunTimeException('Routes file does not exist `' . $routesFile . '`');
        }

        if (!is_writable($routesFile)) {
            throw new RunTimeException('Routes file is not writable `' . $routesFile . '`');
        }

        $contents = file_get_contents($routesFile);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            $ast = $parser->parse($contents);
        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}\n";
            return;
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new RouteScopeVisitor($this->getResources(), $this->prefix));

        $ast = $traverser->traverse($ast);
        $prettyPrinter = new Standard;
        $newCode = $prettyPrinter->prettyPrintFile($ast);

        file_put_contents($routesFile, $newCode);
    }

    /**
     * @return RouteDecorator[]
     */
    private function getResources(): array
    {
        $resources = [];

        foreach ($this->routeDecorators as $decorator) {
            if (isset($routes[$decorator->getController()])) {
                continue;
            }

            $decorator->setTemplate(str_replace('/:id', '', $decorator->getTemplate()));

            $resources[$decorator->getController()] = $decorator;
        }

        return array_values($resources);
    }

    /**
     * @return string
     */
    private function getTemplateFilePath(): string
    {
        return __DIR__ . DS . '..' . DS . 'assets' . DS . 'routes.php';
    }
}
