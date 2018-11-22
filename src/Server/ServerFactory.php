<?php
namespace Lawoole\Server;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Lawoole\Contracts\Server\Factory as FactoryContract;
use Lawoole\Server\Process\Process;
use Lawoole\Server\ServerSockets\HttpServerSocket;
use Lawoole\Server\ServerSockets\ServerSocket;
use Lawoole\Server\ServerSockets\UdpServerSocket;
use Lawoole\Server\ServerSockets\UnixServerSocket;
use Lawoole\Server\ServerSockets\WebSocketServerSocket;

class ServerFactory implements FactoryContract
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Create a new server factory instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Establish a server based on the configuration.
     *
     * @param array $config
     *
     * @return \Lawoole\Contracts\Server\Server
     */
    public function make(array $config)
    {
        $driver = $this->getDriver($config);

        if (! method_exists($this, $method = 'create'.Str::studly($driver).'Driver')) {
            throw new InvalidArgumentException("Driver [{$driver}] is not supported.");
        }

        $server = $this->$method($config);

        $this->configureServer($server, $config);

        return $server;
    }

    /**
     * Get the driver type of the server.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getDriver(array $config)
    {
        return $config['driver'] ?? 'tcp';
    }

    /**
     * Create a Tcp server.
     *
     * @param array $config
     *
     * @return \Lawoole\Server\Server
     */
    protected function createTcpDriver(array $config)
    {
        return new Server($this->app, $this->getDefaultServerSocket($config), $config);
    }

    /**
     * Create a Http server.
     *
     * @param array $config
     *
     * @return \Lawoole\Server\HttpServer
     */
    protected function createHttpDriver(array $config)
    {
        return new HttpServer($this->app, $this->getDefaultServerSocket($config), $config);
    }

    /**
     * Create a WebSocket server.
     *
     * @param array $config
     *
     * @return \Lawoole\Server\WebSocketServer
     */
    protected function createWebsocketDriver(array $config)
    {
        return new WebSocketServer($this->app, $this->getDefaultServerSocket($config), $config);
    }

    /**
     * Create a default server socket instance for the server.
     *
     * @param array $config
     *
     * @return \Lawoole\Server\ServerSockets\ServerSocket
     */
    protected function getDefaultServerSocket(array $config)
    {
        $unixSock = $config['unix_sock'] ?? $this->getDefaultUnixSock();

        $serverSocket = new UnixServerSocket($this->app, [
            'unix_sock' => $unixSock
        ]);

        $serverSocket->setEventHandler(new ServerEventHandler);

        return $serverSocket;
    }

    /**
     * Generate a unix sock file path for default server socket.
     *
     * @return string
     */
    protected function getDefaultUnixSock()
    {
        return $this->app->storagePath().'/framework/server-'.Str::random(8).'.sock';
    }

    /**
     * Get event handler instance.
     *
     * @param array $config
     *
     * @return mixed
     */
    protected function parseEventHandler(array $config)
    {
        return isset($config['handler']) ? $this->app->make($config['handler']) : null;
    }

    /**
     * Configure the server.
     *
     * @param \Lawoole\Server\Server $server
     * @param array $config
     *
     * @return \Lawoole\Server\Server
     */
    protected function configureServer($server, array $config)
    {
        if ($handler = $this->parseEventHandler($config)) {
            $server->setEventHandler($handler);
        }

        if (isset($config['listens']) && $config['listens']) {
            $this->configureServerSockets($server, $config['listens']);
        }

        if (isset($config['processes']) && $config['processes']) {
            $this->configureProcesses($server, $config['processes']);
        }

        return $server;
    }

    /**
     * Configure the server sockets in the server.
     *
     * @param \Lawoole\Server\Server $server
     * @param array $serverSockets
     */
    protected function configureServerSockets($server, array $serverSockets)
    {
        foreach ($serverSockets as $config) {
            $serverSocket = $this->createServerSocket($config);

            if ($handler = $this->parseEventHandler($config)) {
                $serverSocket->setEventHandler($handler);
            }

            $server->listen($serverSocket);
        }
    }

    /**
     * Create a server socket instance.
     *
     * @param array $config
     *
     * @return \Lawoole\Server\ServerSockets\ServerSocket
     */
    protected function createServerSocket(array $config)
    {
        $protocol = $config['protocol'] ?? 'tcp';

        if ($this->app->bound($key = "server.sockets.{$protocol}")) {
            $serverSocket = $this->app->make($key);

            if (is_object($serverSocket) && $serverSocket instanceof ServerSocket) {
                return $serverSocket;
            }

            return $this->app->make($serverSocket, ['config' => $config]);
        }

        if (method_exists($this, $method = 'create'.Str::studly($protocol).'ServerSocket')) {
            return $this->$method($config);
        }

        throw new InvalidArgumentException("The protocol [{$protocol}] is not supported.");
    }

    /**
     * Create a Tcp server socket.
     *
     * @param array $config
     *
     * @return \Lawoole\Server\ServerSockets\ServerSocket
     */
    protected function createTcpServerSocket(array $config)
    {
        return new ServerSocket($this->app, $config);
    }

    /**
     * Create a Udp server socket.
     *
     * @param array $config
     *
     * @return \Lawoole\Server\ServerSockets\UdpServerSocket
     */
    protected function createUdpServerSocket(array $config)
    {
        return new UdpServerSocket($this->app, $config);
    }

    /**
     * Create a Http server socket.
     *
     * @param array $config
     *
     * @return \Lawoole\Server\ServerSockets\HttpServerSocket
     */
    protected function createHttpServerSocket(array $config)
    {
        return new HttpServerSocket($this->app, $config);
    }

    /**
     * Create a WebSocket server socket.
     *
     * @param array $config
     *
     * @return \Lawoole\Server\ServerSockets\ServerSocket
     */
    protected function createWebsocketServerSocket(array $config)
    {
        return new WebSocketServerSocket($this->app, $config);
    }

    /**
     * Create a Unix server socket.
     *
     * @param array $config
     *
     * @return \Lawoole\Server\ServerSockets\ServerSocket
     */
    protected function createUnixServerSocket(array $config)
    {
        return new UnixServerSocket($this->app, $config);
    }

    /**
     * Configure the processes in the server.
     *
     * @param \Lawoole\Server\Server $server
     * @param array $processes
     */
    protected function configureProcesses($server, array $processes)
    {
        foreach ($processes as $config) {
            $process = $this->createProcess($config);

            if ($handler = $this->parseEventHandler($config)) {
                $process->setEventHandler($handler);
            }

            $server->fork($process);
        }
    }

    /**
     * Create a server process.
     *
     * @param array $config
     *
     * @return \Lawoole\Server\Process\Process
     */
    protected function createProcess(array $config)
    {
        return new Process($this->app, $config);
    }
}
