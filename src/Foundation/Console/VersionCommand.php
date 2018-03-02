<?php
namespace Lawoole\Foundation\Console;

use Illuminate\Console\Command;

class VersionCommand extends Command
{
    /**
     * 命令名
     *
     * @var string
     */
    protected $name = 'version';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Get the version of the application';

    /**
     * 执行命令
     */
    public function handle()
    {
        $version = $this->laravel->version();

        $this->info($version);
    }
}
