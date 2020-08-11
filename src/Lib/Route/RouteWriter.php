<?php
declare(strict_types=1);

namespace MixerApi\Rest\Lib\Route;

use MixerApi\Rest\Lib\Exception\RunTimeException;
use MixerApi\Rest\Lib\Parser\RouteScopeVisitor;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Cake\Utility\Text;

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
     * @var MixerApi\Rest\Lib\Controller\ReflectedControllerDecorator[]
     */
    private $resources;

    /**
     * @var string
     */
    private $baseNamespace;

    /**
     * @var string
     */
    private $configDir;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param \MixerApi\Rest\Lib\Controller\ReflectedControllerDecorator[] $controllers ReflectedControllerDecorator[]
     * @param string $baseNamespace a base namespace
     * @param string $configDir an absolute directory path to userland CakePHP config
     * @param string $prefix route prefix (e.g `/`)
     */
    public function __construct(array $resources, string $baseNamespace, string $configDir, string $prefix)
    {
        if (!is_dir($configDir)) {
            throw new RunTimeException("Directory does not exist `$configDir`");
        }

        $this->resources = $resources;
        $this->baseNamespace = $baseNamespace;
        $this->configDir = $configDir;
        $this->prefix = $prefix; // @todo needed for future scope implementation
    }

    /**
     * Merges routes into an existing scope in routes.php
     *
     * @param string $file the routes.php file (mainly used for unit testing)
     * @return void
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
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($contents);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new RouteScopeVisitor($this));

        $ast = $traverser->traverse($ast);
        $code = (new Standard())->prettyPrintFile($ast);

        file_put_contents($routesFile, $code);
    }

    /**
     * @return \MixerApi\Rest\Lib\Controller\ReflectedControllerDecorator[]
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * @return string
     */
    public function getBaseNamespace(): string
    {
        return $this->baseNamespace;
    }

    /**
     * @return string
     */
    public function getConfigDir(): string
    {
        return $this->configDir;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
