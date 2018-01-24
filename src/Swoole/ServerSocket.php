<?php
namespace Lawoole\Swoole;

use IteratorAggregate;
use RuntimeException;
use Swoole\Server\Port;

class ServerSocket implements IteratorAggregate
{
    use HasEventHandlers;

    /**
     * 服务对象
     *
     * @var \Lawoole\Swoole\Server
     */
    protected $server;

    /**
     * 监听地址
     *
     * @var string
     */
    protected $host;

    /**
     * 监听端口
     *
     * @var int
     */
    protected $port;

    /**
     * Socket 类型
     *
     * @var int
     */
    protected $type;

    /**
     * Swoole 服务端口对象
     *
     * @var \Swoole\Server\Port
     */
    protected $swooleServerPort;

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
     * @param string $host
     * @param int $port
     * @param int $type
     * @param array $options
     */
    public function __construct($host, $port, $type = SWOOLE_SOCK_TCP, array $options = [])
    {
        $this->host = $host;
        $this->port = $port;
        $this->type = $type;

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
     * 获得监听地址
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * 获得监听端口
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * 获得 Socket 类型
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 获得 Swoole 服务对象
     *
     * @return \Swoole\Server
     */
    public function getSwooleServer()
    {
        return $this->server->getSwooleServer();
    }

    /**
     * 获得 Swoole 服务端口对象
     *
     * @return \Swoole\Server\Port
     */
    public function getSwooleServerPort()
    {
        return $this->swooleServerPort;
    }

    /**
     * 设置服务 Socket 选项
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        if ($this->server && $this->server->isServing()) {
            throw new RuntimeException('Options cannot be set while the server is serving.');
        }

        $this->options = array_diff_key($this->options, $options) + $options;

        // 如果已经绑定到服务，则同时更改 Swoole 端口对象的配置
        if ($this->swooleServerPort) {
            $this->swooleServerPort->set($this->options);
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
     * @param \Lawoole\Swoole\Server $server
     * @param \Swoole\Server\Port $swooleServerPort
     */
    public function bindToServer(Server $server, Port $swooleServerPort)
    {
        $this->server = $server;
        $this->swooleServerPort = $swooleServerPort;

        // 更新端口配置
        $this->swooleServerPort->set($this->options);

        // 继承异常处理器
        if ($this->getExceptionHandler() == null) {
            $this->setExceptionHandler($server->getExceptionHandler());
        }

        // 注册事件回调
        $this->registerEventCallbacks($this->serverSocketEvents);
    }

    /**
     * 获得当前服务 Socket 所有连接的迭代器
     *
     * @return \Swoole\Connection\Iterator
     */
    public function getConnectionIterator()
    {
        return $this->swooleServerPort->connections;
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
     * 获得当前服务所有连接的迭代器
     *
     * @return \Swoole\Connection\Iterator
     */
    public function getIterator()
    {
        return $this->getConnectionIterator();
    }
}
