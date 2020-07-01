<?php
declare(strict_types=1);

namespace RestBaker;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;

/**
 * Class Plugin
 * @package App
 */
class Plugin extends BasePlugin
{
    public function bootstrap(PluginApplicationInterface $app) : void
    {
        parent::bootstrap($app);
    }

    public function console(CommandCollection $commands): CommandCollection
    {
        return $commands;
    }
}