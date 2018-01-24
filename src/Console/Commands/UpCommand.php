<?php
namespace Lawoole\Console\Commands;

use Illuminate\Console\Command;

class UpCommand extends Command
{
    /**
     * 命令名
     *
     * @var string
     */
    protected $name = 'up';

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
        @unlink($this->laravel->storagePath('framework/down.lock'));

        $this->info('Application is now live.');
    }
}
