<?php
namespace Lawoole\Server\ServerSockets;

use EmptyIterator;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Arr;
use IteratorAggregate;
use Lawoole\Contracts\Server\ServerSocket as ServerSocketContract;
use Lawoole\Server\Server;
use RuntimeException;
use Swoole\Server\Port;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class ServerSocket implements ServerSocketContract, IteratorAggregate
{
    /**
     * 服务对象
     *
     * @var \Lawoole\Contracts\Server\Server
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
    protected $socketType;

    /**
     * 事件处理器
     *
     * @var \Lawoole\Server\ServerSockets\ServerSocketHandler
     */
    protected $handler;

    /**
     * 控制台输出
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * 异常处理器
     *
     * @var \Illuminate\Contracts\Debug\ExceptionHandler
     */
    protected $exceptions;

    /**
     * Swoole 端口对象
     *
     * @var \Swoole\Server\Port
     */
    protected $swoolePort;

    /**
     * 配置选项
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
    protected $serverEvents = [
        'Connect', 'Close', 'Receive', 'Packet', 'BufferFull', 'BufferEmpty'
    ];

    /**
     * 创建服务 Socket 对象
     *
     * @param string $host
     * @param int $port
     * @param array $options
     */
    public function __construct($host, $port, array $options = [])
    {
        $this->host = $host;
        $this->port = $port;

        $this->socketType = Arr::pull($options, 'options', function () {
            return $this->getDefaultSocketType();
        });

        $this->setOptions($options);
    }

    /**
     * 获得默认的 Socket 类型
     *
     * @return int
     */
    protected function getDefaultSocketType()
    {
        return SWOOLE_SOCK_TCP;
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
    public function getSocketType()
    {
        return $this->socketType;
    }

    /**
     * 获得服务对象
     *
     * @return \Lawoole\Contracts\Server\Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * 获得 Swoole 端口对象
     *
     * @return \Swoole\Server\Port
     */
    public function getSwoolePort()
    {
        return $this->swoolePort;
    }

    /**
     * 设置配置选项
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        if ($this->server && $this->server->isServing()) {
            throw new RuntimeException('Options cannot be set while the server is serving');
        }

        $this->options = array_diff_key($this->options, $options) + $options;

        // 如果已经绑定到服务，则同时更改 Swoole 端口对象的配置
        if ($this->swoolePort != null) {
            $this->swoolePort->set($this->options);
        }
    }

    /**
     * 获得配置选项
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * 设置异常处理器
     *
     * @param \Illuminate\Contracts\Debug\ExceptionHandler $exceptions
     */
    public function setExceptionHandler(ExceptionHandler $exceptions)
    {
        $this->exceptions = $exceptions;
    }

    /**
     * 获得异常处理器
     *
     * @return \Illuminate\Contracts\Debug\ExceptionHandler
     */
    public function getExceptionHandler()
    {
        return $this->exceptions;
    }

    /**
     * 设置控制台输出
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * 获得控制台输出
     *
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * 监听服务对象
     *
     * @param \Lawoole\Server\Server $server
     * @param \Swoole\Server\Port $swoolePort
     */
    public function listen(Server $server, Port $swoolePort = null)
    {
        if ($this->isBound()) {
            throw new RuntimeException('ServerSocket can be bound to server only once.');
        }

        if ($swoolePort == null) {
            // 如果传入空的 Swoole 端口对象，则调用服务的监听方法以产生端口对象
            $server->listen($this);

            return;
        }

        $this->server = $server;

        $this->swoolePort->set($this->options);

        $this->registerEventCallbacks($this->serverEvents);

        $this->dispatchEvent('Bind', $server, $this);
    }

    /**
     * 判断是否已经绑定到服务
     *
     * @return bool
     */
    public function isBound()
    {
        return $this->server != null;
    }

    /**
     * 暴露服务 Socket
     */
    public function export()
    {
        $this->dispatchEvent('Export', $this->server, $this);
    }

    /**
     * 获得所有当前连接的迭代器
     *
     * @return \Iterator
     */
    public function getConnectionIterator()
    {
        if ($this->isBound() && $this->server->isServing()) {
            return $this->swoolePort->connections;
        }

        return new EmptyIterator;
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

    /**
     * 设置处理器
     *
     * @param \Lawoole\Server\ServerSockets\ServerSocketHandler
     */
    public function setHandler(ServerSocketHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * 获得处理器
     *
     * @return \Lawoole\Server\ServerSockets\ServerSocketHandler
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * 注册事件回调
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
     * 分发事件
     *
     * @param string $event
     * @param array $arguments
     */
    public function dispatchEvent($event, ...$arguments)
    {
        if ($this->handler == null) {
            return;
        }

        try {
            if (method_exists($this->handler, $method = "on{$event}")) {
                call_user_func_array([$this->handler, $method], $arguments);
            }
        } catch (Exception $e) {
            $this->handleException($e);
        } catch (Throwable $e) {
            $this->handleException(new FatalThrowableError($e));
        }
    }

    /**
     * 注册事件回调
     */
    protected function registerConnectCallback()
    {
        $this->swoolePort->on('Connect', function ($server, $fd, $reactorId) {
            $this->dispatchEvent('Connect', $this->server, $this, $fd, $reactorId);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerCloseCallback()
    {
        $this->swoolePort->on('Close', function ($server, $fd, $reactorId) {
            $this->dispatchEvent('Close', $this->server, $this, $fd, $reactorId);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerReceiveCallback()
    {
        $this->swoolePort->on('Receive', function ($server, $fd, $reactorId, $data) {
            $this->dispatchEvent('Receive', $this->server, $this, $fd, $reactorId, $data);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerPacketCallback()
    {
        $this->swoolePort->on('Packet', function ($server, $data, $clientInfo) {
            $this->dispatchEvent('Packet', $this->server, $this, $data, $clientInfo);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerBufferFullCallback()
    {
        $this->swoolePort->on('BufferFull', function ($server, $fd) {
            $this->dispatchEvent('BufferFull', $this->server, $this, $fd);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerBufferEmptyCallback()
    {
        $this->swoolePort->on('BufferEmpty', function ($server, $fd) {
            $this->dispatchEvent('BufferEmpty', $this->server, $this, $fd);
        });
    }

    /**
     * 处理异常
     *
     * @param \Exception $e
     */
    protected function handleException(Exception $e)
    {
        if ($this->exceptions) {
            $this->exceptions->report($e);

            if ($this->output) {
                $this->exceptions->renderForConsole($this->output, $e);
            }
        }
    }
}
