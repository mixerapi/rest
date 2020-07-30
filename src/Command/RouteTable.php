<?php
declare(strict_types=1);

namespace MixerApi\Rest\Command;

use Cake\Console\ConsoleIo;

/**
 * Class RouteTable
 *
 * Write Ascii Table of Routes to console
 *
 * @package MixerApi\Rest\Command
 */
class RouteTable
{
    /**
     * @var \Cake\Console\ConsoleIo
     */
    private $io;

    /**
     * @var \MixerApi\Rest\Lib\Route\RouteDecorator[]
     */
    private $routeDecorators;

    /**
     * @param \Cake\Console\ConsoleIo $io ConsoleIo
     * @param \MixerApi\Rest\Lib\Route\RouteDecorator[] $routeDecorators Array of RouteDecorator
     */
    public function __construct(ConsoleIo $io, array $routeDecorators)
    {
        $this->io = $io;
        $this->routeDecorators = $routeDecorators;
    }

    /**
     * Write Ascii Table of Routes to console
     *
     * @return void
     */
    public function output(): void
    {
        $output = [
            ['Route name', 'URI template', 'Method(s)', 'Controller', 'Action', 'Plugin'],
        ];

        foreach ($this->routeDecorators as $route) {
            $output[] = [
                $route->getName(),
                $route->getTemplate(),
                implode(', ', $route->getMethods()),
                $route->getController(),
                $route->getAction(),
                $route->getPlugin(),
            ];
        }

        $this->io->helper('table')->output($output);
    }
}
