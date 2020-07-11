<?php

namespace MixerApiRest\Command;

use Cake\Console\ConsoleIo;
use MixerApiRest\Lib\Route\RouteDecorator;

/**
 * Class RouteTable
 *
 * Write Ascii Table of Routes to console
 *
 * @package MixerApiRest\Command
 */
class RouteTable
{
    /**
     * @var ConsoleIo
     */
    private $io;

    /**
     * @var \MixerApiRest\Lib\Route\RouteDecorator[]
     */
    private $routeDecorators;

    /**
     * @param ConsoleIo $io ConsoleIo
     * @param \MixerApiRest\Lib\Route\RouteDecorator[] $routeDecorators Array of RouteDecorator
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