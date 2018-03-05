<?php
namespace Lawoole\Server;

use Illuminate\Contracts\Debug\ExceptionHandler;
use InvalidArgumentException;
use Lawoole\Contracts\Server\Factory;
use Lawoole\Server\ServerSockets\HttpServerSocket;
use Lawoole\Server\ServerSockets\ServerSocket;
use Lawoole\Server\ServerSockets\UdpServerSocket;
use Lawoole\Server\ServerSockets\UnixServerSocket;
use Lawoole\Server\ServerSockets\WebSocketServerSocket;

class ServerManager implements Factory
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * 服务驱动名
     *
     * @var string
     */
    protected $driver;

    /**
     * 服务配置
     *
     * @var array
     */
    protected $config;

    /**
     * 服务对象
     *
     * @var \Lawoole\Contracts\Server\Server
     */
    protected $server;

    /**
     * 创建 Swoole 服务管理器
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     * @param string $driver
     * @param array $config
     */
    public function __construct($app, $driver, array $config)
    {
        $this->app = $app;
        $this->driver = $driver;
        $this->config = $config;
    }

    /**
     * 获得服务对象
     *
     * @return \Lawoole\Contracts\Server\Server
     */
    public function server()
    {
        if ($this->server != null) {
            return $this->server;
        }

        return $this->server = $this->resolveServer();
    }

    /**
     * 解析服务对象
     *
     * @return \Lawoole\Contracts\Server\Server
     */
    public function resolveServer()
    {
        $serverSockets = $this->createServerSockets();

        $defaultServerSocket = head($serverSockets);

        $server = $this->createServer($defaultServerSocket);

        return $this->configureServer($server, $serverSockets);
    }

    /**
     * 创建服务对象
     *
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     *
     * @return \Lawoole\Server\Server
     */
    protected function createServer(ServerSocket $serverSocket)
    {
        $processMode = $this->getProcessMode();

        switch ($this->driver) {
            case 'websocket':
                $server = new WebSocketServer($serverSocket, $processMode);
                break;
            case 'http':
                $server = new HttpServer($serverSocket, $processMode);
                break;
            case 'tcp':
            default:
                $server = new Server($serverSocket, $processMode);
                break;
        }

        return $server;
    }

    /**
     * 获得进程模式
     *
     * @return int
     */
    protected function getProcessMode()
    {
        $mode = $this->config['mode'] ?? 'process';

        switch ($mode) {
            case 'base':
                return SWOOLE_BASE;
            case 'process':
                return SWOOLE_PROCESS;
            default:
                return $mode;
        }
    }

    /**
     * 配置服务
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket[] $serverSockets
     *
     * @return \Lawoole\Server\Server
     */
    protected function configureServer($server, array $serverSockets)
    {
        $server->setOptions($this->config['options'] ?? []);

        $exceptionHandler = $this->app->make(ExceptionHandler::class);

        $server->setExceptionHandler($exceptionHandler);

        foreach ($serverSockets as $serverSocket) {
            $serverSocket->setExceptionHandler($exceptionHandler);

            if (!$serverSocket->isBound()) {
                $server->listen($serverSocket);
            }
        }

        return $server;
    }

    /**
     * 创建服务 Socket
     *
     * @return array
     */
    protected function createServerSockets()
    {
        $listens = $this->config['listens'] ?? null;

        if (empty($listens)) {
            throw new InvalidArgumentException('A port listen must be given at least.');
        }

        $serverSockets = [];

        foreach ((array) $listens as $listen) {
            $serverSocket = $this->createServerSocket($listen);

            $serverSocket->setHandler($this->app->make($listen['handler']));

            if (isset($listen['default']) && $listen['default']) {
                array_unshift($serverSockets, $serverSocket);
            } else {
                $serverSockets[] = $serverSocket;
            }
        }

        return $serverSockets;
    }

    /**
     * 创建服务 Socket
     *
     * @param array $config
     *
     * @return \Lawoole\Server\ServerSockets\ServerSocket
     */
    protected function createServerSocket(array $config)
    {
        $options = $config['options'] ?? [];

        switch ($config['protocol']) {
            case 'tcp':
                return new ServerSocket($config['host'], $config['port'], $options);
            case 'udp':
                return new UdpServerSocket($config['host'], $config['port'], $options);
            case 'http':
                return new HttpServerSocket($config['host'], $config['port'], $options);
            case 'websocket':
                return new WebSocketServerSocket($config['host'], $config['port'], $options);
            case 'unix':
                return new UnixServerSocket($config['unix_sock'], $options);
            default:
                throw new InvalidArgumentException("The protocol [{$config['protocol']}] is not support");
        }
    }

    /**
     * 启动并运行服务
     */
    public function run()
    {
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
            'pid' => getmypid()
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
        return $this->server()->$method(...$parameters);
    }
}
