<?php
namespace Lawoole\Console\Commands;

use Illuminate\Console\Command;

class AppUpCommand extends Command
{
    /**
     * 命令名
     *
     * @var string
     */
    protected $name = 'app:up';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Bring the application out of maintenance mode';

    /**
     * 执行命令
     */
    public function handle()
    {
        @unlink($this->laravel->storagePath('framework/down'));

        $this->info('Application is now live.');
    }
}
