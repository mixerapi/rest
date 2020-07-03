<?php
declare(strict_types=1);

namespace RestBaker\Test\App;

use Cake\Console\CommandCollection;
use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Bake\Command\EntryCommand;
use Cake\Routing\RouteBuilder;

class Application extends BaseApplication
{
    public function middleware(MiddlewareQueue $middleware): MiddlewareQueue
    {
        return $middleware;
    }

    public function bootstrap(): void
    {
        $this->addPlugin('Bake');
        $this->addPlugin('RestBaker');

    }

    public function console(CommandCollection $commands): CommandCollection
    {
        $commands->add('bake', EntryCommand::class);
        return $commands;
    }

    public function routes(RouteBuilder $routes): void
    {

    }
}
