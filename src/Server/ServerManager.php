<?php
namespace Lawoole\Server;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Lawoole\Console\OutputStyle;
use Lawoole\Server\Schedule\ScheduleProcessHandler;
use Lawoole\Swoole\HttpServerSocket;
use Lawoole\Swoole\Process;
use Lawoole\Swoole\ServerSocket;
use Lawoole\Swoole\WebSocketServer;
use Lawoole\Swoole\WebSocketServerSocket;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ServerManager
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\ApplicationInterface
     */
    protected $app;

    /**
     * 是否已经准备服务
     *
     * @var bool
     */
    protected $prepared = false;

    /**
     * Swoole 服务
     *
     * @var \Lawoole\Swoole\Server
     */
    protected $server;

    /**
     * 创建 Swoole 服务管理器
     *
     * @param \Lawoole\Contracts\Foundation\ApplicationInterface $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 获得 Swoole 服务
     *
     * @return \Lawoole\Swoole\Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * 准备 Swoole 服务
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function prepare(InputInterface $input, OutputInterface $output)
    {
        if ($this->prepared) {
            return;
        }

        $this->prepared = true;

        if (!$this->app->bound(OutputStyle::class)) {
            // 共享一个统一的输出
            $this->app->instance(OutputStyle::class, new OutputStyle($input, $output));
        }

        $config = $this->app->make('config')->get('server', []);

        $server = new WebSocketServer($config);

        $this->configureServer($server, $config);

        $this->server = $server;
    }

    /**
     * 配置服务
     *
     * @param \Lawoole\Swoole\Server $server
     * @param array $config
     */
    protected function configureServer($server, array $config)
    {
        $server->setExceptionHandler(
            $this->app->make(Arr::get($config, 'exception.handler', ServerExceptionHandler::class))
        );

        $server->setEventHandler(
            $this->app->make(Arr::get($config, 'handler', ServerHandler::class))
        );

        // 定时脚本处理进程
        if (Arr::get($config, 'schedule')) {
            $this->enableSchedule($server);
        }

        // 后台运行
        if (Arr::get($config, 'daemon')) {
            $server->setOptions(['daemonize' => true]);
        }

        // 端口监听
        $listens = Arr::get($config, 'listens', []);
        foreach ($listens as $listen) {
            $this->addServerSocket($server, $listen);
        }
    }

    /**
     * 支持定时脚本
     *
     * @param \Lawoole\Swoole\Server $server
     */
    protected function enableSchedule($server)
    {
        $process = new Process;

        $process->setExceptionHandler($server->getExceptionHandler());

        $process->setEventHandler(
            $this->app->make(ScheduleProcessHandler::class)
        );

        $server->addProcess($process);
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
            $serverSocket->setEventHandler($this->app->make($handler));
        } elseif ($handlers = Arr::get($config, 'handlers')) {
            foreach ($handlers as $handler) {
                $serverSocket->putEventHandler($this->app->make($handler));
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

        if (method_exists($this, $method = 'create'.ucfirst($protocol).'ServerSocket')) {
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
        $serverSocket->putEventHandler(new TcpServerSocketHandler($this->app));

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
        $serverSocket->putEventHandler(new HttpServerSocketHandler($this->app));

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
     * 启动并运行服务
     */
    public function run()
    {
        if (!$this->prepared) {
            $this->prepare(new ArgvInput, new ConsoleOutput);
        }

        $this->saveRuntime();

        $this->server->serve();

        $this->removeRuntime();
    }

    /**
     * 保存服务运行时信息
     */
    protected function saveRuntime()
    {
        $payload = [
            'pid'       => getmypid(),
            'unix_sock' => $this->server->getUnixSock()
        ];

        file_put_contents(
            $this->app->storagePath('framework/server.runtime'),
            json_encode($payload, JSON_PRETTY_PRINT)
        );
    }

    /**
     * 移除运行时信息
     */
    protected function removeRuntime()
    {
        @unlink($this->app->storagePath('framework/server.runtime'));
    }

    /**
     * 代理调用到 Swoole 服务
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->server->$method(...$parameters);
    }
}
