<?php
namespace Lawoole\Server\Commands;

use Illuminate\Console\Command;

class StartCommand extends Command
{
    /**
     * 命令名
     *
     * @var string
     */
    protected $signature = 'start {--d|daemon= : Run the server in background. }';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Start the Swoole server';

    /**
     * 执行命令
     */
    public function handle()
    {
        $server = $this->laravel->make('server');

        if ($this->option('daemon')) {
            $server->setOptions(['daemon' => true]);
        }

        $server->serve();
    }
}
