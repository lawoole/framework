<?php
namespace Lawoole\Console;

use Illuminate\Console\Application as BaseApplication;
use Illuminate\Contracts\Console\Application as ApplicationContract;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication implements ApplicationContract
{
    /**
     * {@inheritdoc}
     */
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
    {
        $this->laravel->instance('console.command', $command);

        parent::doRunCommand($command, $input, $output);

        $this->laravel->forgetInstance('console.command');
    }
}