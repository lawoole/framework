<?php
namespace Lawoole\Server\Commands;

use Illuminate\Console\Command;

class ShutdownCommand extends Command
{
    /**
     * 命令名
     *
     * @var string
     */
    protected $signature = 'shutdown';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Stop the Swoole server';

    /**
     * 执行命令
     */
    public function handle()
    {

    }
}
