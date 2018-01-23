<?php
namespace Lawoole\Server\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Lawoole\Console\OutputStyle;
use Lawoole\Server\HttpServerSocketHandler;
use Lawoole\Server\ServerExceptionHandler;
use Lawoole\Server\ServerHandler;
use Lawoole\Server\TcpServerSocketHandler;
use Lawoole\Swoole\HttpServerSocket;
use Lawoole\Swoole\ServerSocket;
use Lawoole\Swoole\WebSocketServer;
use Lawoole\Swoole\WebSocketServerSocket;

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
