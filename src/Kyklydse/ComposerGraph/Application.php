<?php
namespace Kyklydse\ComposerGraph;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new GraphCommand();
        return $commands;
    }
}
