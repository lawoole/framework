<?php
namespace Lawoole\Swoole\Commands;

use Illuminate\Console\Command;

class ServeCommand extends Command
{
    /**
     * 命令名
     *
     * @var string
     */
    protected $signature = 'serve';

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

    }
}
