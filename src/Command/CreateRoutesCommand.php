<?php
declare(strict_types=1);

namespace MixerApiRest\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use MixerApiRest\Lib\Controller\ControllerUtility;
use MixerApiRest\Lib\Controller\ReflectedControllerDecorator;
use MixerApiRest\Lib\Exception\InvalidControllerException;
use MixerApiRest\Lib\Route\RouteDecoratorFactory;
use MixerApiRest\Lib\Route\RouteWriter;

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
            ]);

        if (defined('TEST_APP')) {
            $parser->addOption('routesFile', [
                'help' => 'Specifies a name for the routes file, for testing only'
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
            $configDir = reset($plugins) . DS . $args->getOption('plugin');
        } else {

            $namespace = Configure::read('App.namespace');
            $configDir = CONFIG;
        }

        $controllers = $plugin ?? ControllerUtility::getControllersFqn($namespace);

        if (empty($controllers)) {
            $io->warning("> No controllers were found in namespace `$namespace`");
            $this->abort();
        }

        $decoratedControllers = [];

        foreach ($controllers as $controllerFqn) {
            try {
                $decoratedControllers[] = new ReflectedControllerDecorator($controllerFqn, $namespace);
            } catch (InvalidControllerException $e) {
                // maybe do something here?
            }
        }

        $routes = [];

        if ($args->getOption('display') === null) {
            if (strtoupper($io->ask('Overwrite existing routes in `' . $configDir . '`?', 'Y')) !== 'Y') {
                $this->abort();
            }

            $file = $args->getOption('routesFile') ?? 'routes.php';

            (new RouteWriter($decoratedControllers, $configDir))->overwrite($file);
            $io->success('> Routes were written to ' . $configDir . $file);
            $io->out();

            return;
        } else {
            $factory = new RouteDecoratorFactory((string)$args->getOption('plugin'));
            foreach ($decoratedControllers as $decorator) {
                $routes = array_merge($routes, $factory->createFromReflectedControllerDecorator($decorator));
            }
        }

        (new RouteTable($io, $routes))->output();
    }
}
