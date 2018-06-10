<?php
namespace Lawoole\Foundation\Console;

use Illuminate\Console\Command;

class VersionCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the version of the application';

    /**
     * 执行命令
     */
    public function handle()
    {
        $this->info("{$this->laravel->name()} {$this->laravel->version()}");
    }
}
