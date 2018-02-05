<?php
namespace Lawoole\Swoole;

use Illuminate\Support\Arr;
use RuntimeException;
use Swoole\Client as SwooleClient;

class Client
{
    use HasEventHandlers;

    /**
     * Swoole 客户端对象
     *
     * @var \Swoole\Client
     */
    protected $swooleClient;

    /**
     * 客户端同步模式
     *
     * @var bool
     */
    protected $synchronized = false;

    /**
     * SSL 模式
     *
     * @var bool
     */
    protected $ssl = false;

    /**
     * 客户端配置
     *
     * @var array
     */
    protected $config;

    /**
     * 连接主机
     *
     * @var string
     */
    protected $host;

    /**
     * 连接端口
     *
     * @var int
     */
    protected $port;

    /**
     * 是否已经打开
     *
     * @var bool
     */
    protected $opened = false;

    /**
     * 是否已建立连接
     *
     * @var bool
     */
    protected $connected = false;

    /**
     * Swoole 客户端选项
     *
     * @var array
     */
    protected $options = [
        'timeout' => 3.0
    ];

    /**
     * 可用事件回调
     *
     * @var array
     */
    protected $serverEvents = [
        'Connect', 'Error', 'Receive', 'Close', 'BufferFull', 'BufferEmpty'
    ];

    /**
     * 创建客户端
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;

        $this->host = Arr::get($config, 'host', '127.0.0.1');
        $this->port = Arr::get($config, 'port', 9501);
        $this->synchronized = Arr::get($config, 'synchronized', false);
        $this->ssl = Arr::get($config, 'ssl', false);

        $this->swooleClient = $this->createSwooleClient();

        if ($options = Arr::get($config, 'options')) {
            $this->setOptions($options);
        }

        if ($this->synchronized == false) {
            $this->registerEventCallbacks($this->serverEvents);
        }
    }

    /**
     * 创建 Swoole 客户端
     *
     * @return \Swoole\Client
     */
    protected function createSwooleClient()
    {
        $sockType = SWOOLE_SOCK_TCP;

        if ($this->ssl) {
            $sockType |= SWOOLE_SSL;
        }

        if (Arr::get($this->config, 'persistent')) {
            $sockType |= SWOOLE_KEEP;
        }

        $reuseKey = Arr::get($this->config, 'reuse_key');

        return new SwooleClient($sockType, !$this->synchronized, $reuseKey);
    }

    /**
     * 获得 Swoole 客户端对象
     *
     * @return \Swoole\Client
     */
    public function getSwooleClient()
    {
        return $this->swooleClient;
    }

    /**
     * 获得连接主机
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * 获得连接端口
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * 设置客户端选项
     *
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        if ($this->opened) {
            throw new RuntimeException('Options cannot be set while the client is opened');
        }

        $this->options = array_diff_key($this->options, $options) + $options;

        $this->swooleClient->set($this->options);
    }

    /**
     * 获得客户端选项
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * 启用 SSL 连接
     */
    public function enableSsl()
    {
        if ($this->synchronized) {
            $this->swooleClient->enableSSL();

            $this->ssl = true;
            $this->dispatchEvent('SslReady', $this);

            return;
        }

        $this->swooleClient->enableSSL(function () {
            $this->ssl = true;
            $this->dispatchEvent('SslReady', $this);
        });
    }

    /**
     * 获得服务端 SSL 证书信息
     *
     * @param bool $allowSelfSigned
     *
     * @return string
     */
    public function getServerCert($allowSelfSigned = false)
    {
        return $this->swooleClient->getPeerCert($allowSelfSigned);
    }

    /**
     * 判断连接是否已经建立
     *
     * @return bool
     */
    public function isConnected()
    {
        $this->connected = $this->swooleClient->isConnected();

        return $this->connected;
    }

    /**
     * 连接服务器
     *
     * @return bool
     */
    public function connect()
    {
        if ($this->opened) {
            return true;
        }

        $this->opened = true;

        $timeout = Arr::get($this->options, 'timeout', 3.0);

        if ($this->synchronized) {
            $result = $this->swooleClient->connect($this->host, $this->port, $timeout);

            if ($result) {
                $this->dispatchEvent('Connect', $this);
            } else {
                $this->close(true);
            }

            return $result;
        }

        return $this->swooleClient->connect($this->host, $this->port, $timeout);
    }

    /**
     * 获得本地 Socket 地址
     *
     * @return string
     */
    public function getLocalAddress()
    {
        if ($this->connected == false) {
            throw new RuntimeException('Socket address cannot be read while the connection has not been created');
        }

        $socketInfo = $this->swooleClient->getsockname();

        return $socketInfo['host'].':'.$socketInfo['port'];
    }

    /**
     * 向服务端发送数据
     *
     * @param string $data
     *
     * @return bool|int
     */
    public function send($data)
    {
        return $this->swooleClient->send($data);
    }

    /**
     * 向指定客户端发送 Udp 数据包
     *
     * @param string $ip
     * @param int $port
     * @param string $data
     *
     * @return bool
     */
    public function sendUdpPacket($ip, $port, $data)
    {
        return $this->swooleClient->sendto($ip, $port, $data);
    }

    /**
     * 向客户端发送文件中的数据
     *
     * @param string $filename
     * @param int $offset
     * @param int $length
     *
     * @return bool
     */
    public function sendFile($filename, $offset = 0, $length = 0)
    {
        return $this->swooleClient->sendfile($filename, $offset, $length);
    }

    /**
     * 接收数据
     *
     * @param int $size
     * @param int $flags
     *
     * @return string
     */
    public function receive($size = 65535, $flags = 0)
    {
        $data = $this->swooleClient->recv($size, $flags);

        if ($data === false) {
            $errCode = $this->swooleClient->errCode;
            $message = swoole_strerror($errCode);

            throw new RuntimeException($message, $errCode);
        }

        if ($data == '' && !$this->isConnected()) {
            throw new RuntimeException('Cannot receive data while the connection is closed');
        }

        $this->dispatchEvent('Receive', $this, $data);

        return $data;
    }

    /**
     * 关闭客户端
     *
     * @param bool $force
     *
     * @return bool
     */
    public function close($force = false)
    {
        if ($this->synchronized) {
            $result = $this->swooleClient->close($force);

            if ($result) {
                $this->opened = false;
                $this->connected = false;

                $this->dispatchEvent('Close', $this);
            }

            return $result;
        }

        return $this->swooleClient->close($force);
    }

    /**
     * 暂停事件监听
     *
     * @return bool
     */
    public function pause()
    {
        $result = $this->swooleClient->resume();

        if ($result) {
            $this->dispatchEvent('Pause', $this);
        }

        return $result;
    }

    /**
     * 恢复事件监听
     *
     * @return bool
     */
    public function resume()
    {
        $result = $this->swooleClient->resume();

        if ($result) {
            $this->dispatchEvent('Resume', $this);
        }

        return $result;
    }

    /**
     * 注册事件回调
     */
    protected function registerConnectCallback()
    {
        $this->swooleClient->on('Connect', function ($client) {
            $this->connected = true;

            $this->dispatchEvent('Connect', $this);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerErrorCallback()
    {
        $this->swooleClient->on('Error', function ($client) {
            $this->dispatchEvent('Error', $this);
            $this->close(true);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerReceiveCallback()
    {
        $this->swooleClient->on('Receive', function ($client, $data) {
            $this->dispatchEvent('Receive', $this, $data);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerCloseCallback()
    {
        $this->swooleClient->on('Close', function ($client) {
            $this->opened = false;
            $this->connected = false;

            $this->dispatchEvent('Close', $this);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerBufferFullCallback()
    {
        $this->swooleClient->on('BufferFull', function ($client) {
            $this->dispatchEvent('BufferFull', $this);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerBufferEmptyCallback()
    {
        $this->swooleClient->on('BufferEmpty', function ($client) {
            $this->dispatchEvent('BufferEmpty', $this);
        });
    }
}
