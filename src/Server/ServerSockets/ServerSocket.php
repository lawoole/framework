<?php
namespace Lawoole\Server\ServerSockets;

use EmptyIterator;
use Illuminate\Support\Arr;
use IteratorAggregate;
use Lawoole\Contracts\Foundation\Application;
use Lawoole\Contracts\Server\ServerSocket as ServerSocketContract;
use Lawoole\Server\Concerns\HasEventHandler;
use Lawoole\Server\Server;
use RuntimeException;
use Swoole\Server\Port;

class ServerSocket implements ServerSocketContract, IteratorAggregate
{
    use HasEventHandler;

    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * 服务对象
     *
     * @var \Lawoole\Swoole\Server
     */
    protected $server;

    /**
     * Swoole 端口对象
     *
     * @var \Swoole\Server\Port
     */
    protected $swoolePort;

    /**
     * 是否已经绑定到服务
     *
     * @var bool
     */
    protected $bound = false;

    /**
     * 服务配置
     *
     * @var array
     */
    protected $config;

    /**
     * 服务 Socket 选项
     *
     * @var array
     */
    protected $options = [
        'tcp_fastopen'     => true,
        'open_tcp_nodelay' => true
    ];

    /**
     * 可用事件回调
     *
     * @var array
     */
    protected $serverSocketEvents = [
        'Connect', 'Close', 'Receive', 'Packet', 'BufferFull', 'BufferEmpty'
    ];

    /**
     * 创建服务 Socket 对象
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     * @param array $config
     */
    public function __construct(Application $app, array $config = [])
    {
        $this->app = $app;

        $options = Arr::pull($config, 'options', []);

        $this->config = $config;

        $this->setOptions($options);
    }

    /**
     * 获得服务对象
     *
     * @return \Lawoole\Swoole\Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * 获得 Swoole 服务端口对象
     *
     * @return \Swoole\Server\Port
     */
    public function getSwoolePort()
    {
        return $this->swoolePort;
    }

    /**
     * 设置服务 Socket 选项
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        if ($this->server && $this->server->isServing()) {
            throw new RuntimeException('Options cannot be set while the server is serving');
        }

        $this->options = array_diff_key($this->options, $options) + $options;

        if ($this->swoolePort) {
            $this->swoolePort->set($this->options);
        }
    }

    /**
     * 获得服务 Socket 选项
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * 绑定到服务对象
     *
     * @param \Lawoole\Server\Server $server
     */
    public function bindToServer(Server $server)
    {
        if ($this->bound) {
            throw new RuntimeException('ServerSocket can be bound to server only once.');
        }

        $this->bound = true;

        $this->server = $server;

        // 监听服务


        $this->swoolePort->set($this->options);

        $this->registerEventCallbacks($this->serverSocketEvents);

        $this->dispatchEvent('Bind', $server, $this);
    }

    /**
     * 添加服务监听
     *
     * @param \Lawoole\Server\Server $server
     *
     * @return \Swoole\Server\Port
     */
    protected function listenToServer(Server $server)
    {
        $swooleServer =
    }

    /**
     * 判断是否已经绑定到服务
     *
     * @return bool
     */
    public function isBound()
    {
        return $this->bound;
    }

    /**
     * 暴露服务 Socket
     */
    public function export()
    {
        $this->dispatchEvent('Export', $this->server, $this);
    }

    /**
     * 注册事件回调
     */
    protected function registerConnectCallback()
    {
        $this->swooleServerPort->on('Connect', function ($server, $fd, $reactorId) {
            $this->dispatchEvent('Connect', $this->server, $this, $fd, $reactorId);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerCloseCallback()
    {
        $this->swooleServerPort->on('Close', function ($server, $fd, $reactorId) {
            $this->dispatchEvent('Close', $this->server, $this, $fd, $reactorId);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerReceiveCallback()
    {
        $this->swooleServerPort->on('Receive', function ($server, $fd, $reactorId, $data) {
            $this->dispatchEvent('Receive', $this->server, $this, $fd, $reactorId, $data);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerPacketCallback()
    {
        $this->swooleServerPort->on('Packet', function ($server, $data, $clientInfo) {
            $this->dispatchEvent('Packet', $this->server, $this, $data, $clientInfo);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerBufferFullCallback()
    {
        $this->swooleServerPort->on('BufferFull', function ($server, $fd) {
            $this->dispatchEvent('BufferFull', $this->server, $this, $fd);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerBufferEmptyCallback()
    {
        $this->swooleServerPort->on('BufferEmpty', function ($server, $fd) {
            $this->dispatchEvent('BufferEmpty', $this->server, $this, $fd);
        });
    }

    /**
     * 获得所有当前连接的迭代器
     *
     * @return \Iterator
     */
    public function getConnectionIterator()
    {
        if ($this->swooleServerPort == null) {
            return new EmptyIterator;
        }

        return $this->swooleServerPort->connections;
    }

    /**
     * 获得当前服务所有连接的迭代器
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        return $this->getConnectionIterator();
    }
}
