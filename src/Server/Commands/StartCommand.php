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
    protected $signature = 'start';

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
        $serverManager = $this->laravel->make('server.manager');

        $serverManager->prepare($this->input, $this->output);

        // 启动服务
        $serverManager->run();
    }
}
