<?php
declare(strict_types=1);

namespace MixerApi\Rest\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Utility\Inflector;
use MixerApi\Rest\Lib\Controller\ControllerUtility;
use MixerApi\Rest\Lib\Route\RouteDecoratorFactory;
use MixerApi\Rest\Lib\Route\RouteWriter;

/**
 * Class RouteCommand
 *
 * @package SwaggerBake\Command
 */
class CreateRoutesCommand extends Command
{
    /**
     * @param \Cake\Console\ConsoleOptionParser $parser ConsoleOptionParser
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Creates RESTful routes')
            ->addOption('display', [
                'help' => 'Display what routes will be created only, will not write to files',
            ])
            ->addOption('plugin', [
                'help' => 'Specify a plugin',
            ])
            ->addOption('namespace', [
                'help' => 'A base namespace (e.g. App\Controller or App\Api\Controller)',
            ])
            ->addOption('prefix', [
                'help' => 'Route prefix (e.g. /api)',
            ]);

        if (defined('TEST_APP')) {
            $parser->addOption('routesFile', [
                'help' => 'Specifies a name for the routes file, for testing only',
            ]);
        }

        return $parser;
    }

    /**
     * List Cake Routes that can be added to Swagger. Prints to console.
     *
     * @param \Cake\Console\Arguments $args Arguments
     * @param \Cake\Console\ConsoleIo $io ConsoleIo
     * @return int|void|null
     * @throws \ReflectionException
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->hr();
        $io->out('| Creating routes ');
        $io->hr();

        if ($args->getOption('plugin')) {
            $namespace = $args->getOption('plugin');
            $plugins = Configure::read('App.paths.plugins');
            $prefix = Inflector::dasherize($namespace);
            $configDir = reset($plugins) . DS . $args->getOption('plugin');
        } else {
            $namespace = Configure::read('App.namespace');
            $prefix = '/';
            $configDir = CONFIG;
        }

        $namespace = $args->getOption('namespace') ?? $namespace . '\Controller';
        $prefix = $args->getOption('prefix') ?? $prefix;

        $controllers = $plugin ?? ControllerUtility::getControllersFqn($namespace);

        if (empty($controllers)) {
            $io->warning("> No controllers were found in namespace `$namespace`");
            $this->abort();
        }

        $decoratedControllers = ControllerUtility::getReflectedControllerDecorators($controllers);

        $routeDecorators = [];

        $factory = new RouteDecoratorFactory($namespace, $prefix, $args->getOption('plugin'));
        foreach ($decoratedControllers as $decorator) {
            $routeDecorators = array_merge(
                $routeDecorators,
                $factory->createFromReflectedControllerDecorator($decorator)
            );
        }

        if ($args->getOption('display') === null) {
            $file = $args->getOption('routesFile') ?? 'routes.php';

            $ask = $io->ask('This will modify`' . $configDir . $file . '`, continue?', 'Y');
            if (strtoupper($ask) !== 'Y') {
                $this->abort();
            }

            (new RouteWriter($routeDecorators, $configDir, $prefix))->merge($file);
            $io->success('> Routes were written to ' . $configDir . $file);
            $io->out();

            return;
        }

        (new RouteTable($io, $routeDecorators))->output();
    }
}
