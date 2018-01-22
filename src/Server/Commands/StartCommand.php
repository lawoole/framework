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
    protected $signature = 'start {--d|daemon : Run the server in background. }';

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
        $config = $this->laravel->make('config')->get('server', []);

        $server = new WebSocketServer($config);

        $this->configureServer($server, $config);

        $this->saveRuntime($server);

        // 启动服务
        $server->serve();
    }

    /**
     * 配置服务
     *
     * @param \Lawoole\Swoole\Server $server
     * @param array $config
     */
    protected function configureServer($server, array $config)
    {
        // 共享一个统一的输出
        $this->laravel->instance(OutputStyle::class, new OutputStyle($this->input, $this->output));

        $server->setExceptionHandler(
            $this->laravel->make(Arr::get($config, 'exception.handler', ServerExceptionHandler::class))
        );

        $server->setEventHandler(
            $this->laravel->make(Arr::get($config, 'handler', ServerHandler::class))
        );

        // 后台运行
        if ($this->option('daemon')) {
            $server->setOptions(['daemonize' => true]);
        }

        // 端口监听
        $listens = Arr::get($config, 'listens', []);
        foreach ($listens as $listen) {
            $this->addServerSocket($server, $listen);
        }
    }

    /**
     * 添加服务 Socket
     *
     * @param \Lawoole\Swoole\Server $server
     * @param array $config
     */
    protected function addServerSocket($server, array $config)
    {
        $serverSocket = $this->createServerSocket($config);

        if ($handler = Arr::get($config, 'handler')) {
            // 如果通过 handler 去设置事件处理器，会覆盖默认的事件处理器
            $serverSocket->setEventHandler($this->laravel->make($handler));
        } elseif ($handlers = Arr::get($config, 'handlers')) {
            foreach ($handlers as $handler) {
                $serverSocket->putEventHandler($this->laravel->make($handler));
            }
        }

        // 服务 Socket 选项
        if ($options = Arr::get($config, 'options')) {
            $serverSocket->setOptions($options);
        }

        // 添加监听
        $server->listen($serverSocket);
    }

    /**
     * 创建服务 Socket
     *
     * @param array $config
     *
     * @return \Lawoole\Swoole\ServerSocket
     */
    protected function createServerSocket(array $config)
    {
        $protocol = Arr::get($config, 'protocol', 'http');

        if (method_exists($this, $method = 'create'.Str::studly($protocol).'ServerSocket')) {
            $serverSocket = $this->$method($config);
        } else {
            throw new InvalidArgumentException("The protocol [{$protocol}] is not support.");
        }

        return $serverSocket;
    }

    /**
     * 创建 Tcp 服务 Socket
     *
     * @param array $config
     *
     * @return \Lawoole\Swoole\ServerSocket
     */
    protected function createTcpServerSocket(array $config)
    {
        $serverSocket = new ServerSocket(
            Arr::get($config, 'host', '127.0.0.1'),
            Arr::get($config, 'port', 9501)
        );

        // 默认处理器
        $serverSocket->putEventHandler(new TcpServerSocketHandler($this->laravel));

        return $serverSocket;
    }

    /**
     * 创建 Http 服务 Socket
     *
     * @param array $config
     *
     * @return \Lawoole\Swoole\HttpServerSocket
     */
    protected function createHttpServerSocket(array $config)
    {
        $serverSocket = new HttpServerSocket(
            Arr::get($config, 'host', '127.0.0.1'),
            Arr::get($config, 'port', 80)
        );

        // 默认处理器
        $serverSocket->putEventHandler(new HttpServerSocketHandler($this->laravel));

        return $serverSocket;
    }

    /**
     * 创建 WebSocket 服务 Socket
     *
     * @param array $config
     *
     * @return \Lawoole\Swoole\WebSocketServerSocket
     */
    protected function createWebSocketServerSocket(array $config)
    {
        $serverSocket = new WebSocketServerSocket(
            Arr::get($config, 'host', '127.0.0.1'),
            Arr::get($config, 'port', 80)
        );

        return $serverSocket;
    }

    /**
     * 保存服务运行时信息
     *
     * @param \Lawoole\Swoole\Server $server
     */
    protected function saveRuntime($server)
    {
        $payload = [
            'unix_sock' => $server->getUnixSock()
        ];

        file_put_contents(
            $this->laravel->storagePath('framework/server.runtime'),
            json_encode($payload, JSON_PRETTY_PRINT)
        );
    }
}
