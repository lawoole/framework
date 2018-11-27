<?php
namespace Lawoole\Server\ServerSockets;

use EmptyIterator;
use Illuminate\Contracts\Foundation\Application;
use Lawoole\Contracts\Server\ServerSocket as ServerSocketContract;
use Lawoole\Server\Server;
use Lawoole\Support\DispatchEvents;
use LogicException;
use Swoole\Server\Port as SwoolePort;

class ServerSocket implements ServerSocketContract
{
    use DispatchEvents;

    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The server instance.
     *
     * @var \Lawoole\Contracts\Server\Server
     */
    protected $server;

    /**
     * The Swoole port instance.
     *
     * @var \Swoole\Server\Port
     */
    protected $swoolePort;

    /**
     * The server socket configurations.
     *
     * @var array
     */
    protected $config;

    /**
     * The server socket options.
     *
     * @var array
     */
    protected $options = [
        'tcp_fastopen'     => true,
        'open_tcp_nodelay' => true
    ];

    /**
     * An array of available server socket events.
     *
     * @var array
     */
    protected $serverEvents = [
        'Connect', 'Close', 'Receive', 'Packet', 'BufferFull', 'BufferEmpty'
    ];

    /**
     * Create a new server socket instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param array $config
     */
    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;

        $this->setOptions($config['options'] ?? []);
    }

    /**
     * Get the host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->config['port'] ?? $this->getDefaultHost();
    }

    /**
     * Get default host for listening.
     *
     * @return string
     */
    protected function getDefaultHost()
    {
        return 'localhost';
    }

    /**
     * Get the port number.
     *
     * @return int
     */
    public function getPort()
    {
        return $this->config['port'] ?? $this->getDefaultPort();
    }

    /**
     * Get default port number for listening.
     *
     * @return int
     */
    protected function getDefaultPort()
    {
        return 6029;
    }

    /**
     * Get the socket type for this server socket.
     *
     * @return int
     */
    public function getSocketType()
    {
        return $this->config['sock_type'] ?? $this->getDefaultSocketType();
    }

    /**
     * Get default socket type for this server socket.
     *
     * @return int
     */
    protected function getDefaultSocketType()
    {
        return SWOOLE_SOCK_TCP;
    }

    /**
     * Get the server.
     *
     * @return \Lawoole\Contracts\Server\Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Get the Swoole port instance.
     *
     * @return \Swoole\Server\Port
     */
    public function getSwoolePort()
    {
        return $this->swoolePort;
    }

    /**
     * Set the server socket options.
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        if ($this->server && $this->server->isRunning()) {
            throw new LogicException('Options cannot be set while the server is running.');
        }

        $this->options = array_diff_key($this->options, $options) + $options;

        if ($this->swoolePort != null) {
            $this->swoolePort->set($this->options);
        }
    }

    /**
     * Get all server socket options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->swoolePort ? $this->swoolePort->setting : $this->options;
    }

    /**
     * Bind the server socket to a server instance.
     *
     * @param \Lawoole\Server\Server $server
     * @param \Swoole\Server\Port $swoolePort
     */
    public function listen(Server $server, SwoolePort $swoolePort = null)
    {
        if ($this->isBound()) {
            throw new LogicException('The server socket can be bound to server only once.');
        }

        if ($swoolePort == null) {
            // If the passed Swoole port is null, that means we are calling this
            // method directly, so here we need to call the server method "listen"
            // to do the actual binding.
            return $server->listen($this);
        }

        $this->server = $server;
        $this->swoolePort = $swoolePort;

        $this->swoolePort->set($this->options);

        $this->registerEventCallbacks($this->serverEvents);

        $this->dispatchEvent('Bind', $server, $this);
    }

    /**
     * Return whether the server socket has been bound to a server.
     *
     * @return bool
     */
    public function isBound()
    {
        return $this->server != null;
    }

    /**
     * Export the server socket and start to handle connection.
     */
    public function export()
    {
        $this->dispatchEvent('Export', $this->server, $this);
    }

    /**
     * Retrieve an iterator for all connections in this port.
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        if ($this->isBound() && $this->server->isRunning()) {
            return $this->swoolePort->connections;
        }

        return new EmptyIterator;
    }

    /**
     * Register all event callbacks.
     *
     * @param array $events
     */
    protected function registerEventCallbacks(array $events)
    {
        foreach ($events as $event) {
            call_user_func([$this, "register{$event}Callback"]);
        }
    }

    /**
     * Register the event callback.
     */
    protected function registerConnectCallback()
    {
        $this->swoolePort->on('Connect', function ($server, $fd, $reactorId) {
            $this->dispatchEvent('Connect', $this->server, $this, $fd, $reactorId);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerCloseCallback()
    {
        $this->swoolePort->on('Close', function ($server, $fd, $reactorId) {
            $this->dispatchEvent('Close', $this->server, $this, $fd, $reactorId);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerReceiveCallback()
    {
        $this->swoolePort->on('Receive', function ($server, $fd, $reactorId, $data) {
            $this->dispatchEvent('Receive', $this->server, $this, $fd, $reactorId, $data);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerPacketCallback()
    {
        $this->swoolePort->on('Packet', function ($server, $data, $clientInfo) {
            $this->dispatchEvent('Packet', $this->server, $this, $data, $clientInfo);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerBufferFullCallback()
    {
        $this->swoolePort->on('BufferFull', function ($server, $fd) {
            $this->dispatchEvent('BufferFull', $this->server, $this, $fd);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerBufferEmptyCallback()
    {
        $this->swoolePort->on('BufferEmpty', function ($server, $fd) {
            $this->dispatchEvent('BufferEmpty', $this->server, $this, $fd);
        });
    }
}
