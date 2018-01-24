<?php
namespace Lawoole\Console\Commands;

use Illuminate\Console\Command;

class AppNameCommand extends Command
{
    /**
     * 命令名
     *
     * @var string
     */
    protected $name = 'app:name';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Get the name of the application';

    /**
     * 执行命令
     */
    public function handle()
    {
        $this->info($this->laravel->version());
    }
}
