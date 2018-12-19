<?php
namespace Lawoole\Server;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Lawoole\Contracts\Server\Factory as FactoryContract;
use Lawoole\Contracts\Server\ServerSocket as ServerSocketContract;
use Lawoole\Server\Process\Process;
use Lawoole\Server\ServerSockets\HttpServerSocket;
use Lawoole\Server\ServerSockets\ServerSocket;
use Lawoole\Server\ServerSockets\UdpServerSocket;
use Lawoole\Server\ServerSockets\UnixServerSocket;
use Lawoole\Server\ServerSockets\WebsocketServerSocket;

class ServerFactory implements FactoryContract
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The custom server resolvers.
     *
     * @var array
     */
    protected $serverExtensions = [];

    /**
     * The custom server socket resolvers.
     *
     * @var array
     */
    protected $serverSocketExtensions = [];

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

        // First we will check by the server driver type to see if an extension
        // has been registered specifically for that server.
        if (isset($this->serverExtensions[$driver])) {
            $server = call_user_func($this->serverExtensions[$driver], $config);
        }

        // Create a built-in server with given driver type.
        elseif (method_exists($this, $method = 'create'.Str::studly($driver).'Driver')) {
            $server = $this->$method($config);
        }

        // Otherwise, if the driver type does not support, an exception will be thrown.
        else {
            throw new InvalidArgumentException("Driver [{$driver}] is not supported.");
        }

        $this->configureServer($server, $config);

        return $server;
    }

    /**
     * Register an extension server resolver.
     *
     * @param string $driver
     * @param callable $resolver
     */
    public function extendServer($driver, callable $resolver)
    {
        $this->serverExtensions[$driver] = $resolver;
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
     * Create a Websocket server.
     *
     * @param array $config
     *
     * @return \Lawoole\Server\WebsocketServer
     */
    protected function createWebsocketDriver(array $config)
    {
        return new WebsocketServer($this->app, $this->getDefaultServerSocket($config), $config);
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
     * Register an extension server socket resolver.
     *
     * @param string $protocol
     * @param callable $resolver
     */
    public function extendServerSocket($protocol, callable $resolver)
    {
        $this->serverSocketExtensions[$protocol] = $resolver;
    }

    /**
     * Create a server socket instance.
     *
     * @param array $config
     *
     * @return \Lawoole\Contracts\Server\ServerSocket
     */
    protected function createServerSocket(array $config)
    {
        $protocol = $config['protocol'] ?? 'tcp';

        if (isset($this->serverSocketExtensions[$protocol])) {
            $serverSocket = call_user_func($this->serverSocketExtensions[$protocol], $this->app, $config);

            if (! $serverSocket instanceof ServerSocketContract) {
                throw new InvalidArgumentException(
                    "The protocol [{$protocol}] resolver must return a ServerSocket instance."
                );
            }

            return $serverSocket;
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
     * Create a Websocket server socket.
     *
     * @param array $config
     *
     * @return \Lawoole\Server\ServerSockets\ServerSocket
     */
    protected function createWebsocketServerSocket(array $config)
    {
        return new WebsocketServerSocket($this->app, $config);
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
