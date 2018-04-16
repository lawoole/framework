<?php
namespace Lawoole\Homer\Transport;

use Illuminate\Support\Facades\Log;
use Lawoole\Homer\HomerException;
use Throwable;

abstract class Client
{
    /**
     * 服务器主机地址
     *
     * @var string
     */
    protected $host;

    /**
     * 服务器端口
     *
     * @var int
     */
    protected $port;

    /**
     * 选项
     *
     * @var array
     */
    protected $options;

    /**
     * 创建客户端
     *
     * @param string $host
     * @param int $port
     * @param array $options
     */
    public function __construct($host, $port, array $options = [])
    {
        $this->host = $host;
        $this->port = $port;
        $this->options = $options;
    }

    /**
     * 获得服务器地址
     *
     * @return string
     */
    public function getRemoteAddress()
    {
        return $this->host.':'.$this->port;
    }

    /**
     * 获得请求超时
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->options['timeout'] ?? 3000;
    }

    /**
     * 获得连接超时
     *
     * @return int
     */
    public function getConnectTimeout()
    {
        return $this->options['connect_timeout'] ?? 3000;
    }

    /**
     * 连接服务器
     */
    public function connect()
    {
        if ($this->isConnected()) {
            return;
        }

        try {
            $this->doConnect();

            if (!$this->isConnected()) {
                throw new HomerException('Failed to connect to server '.$this->getRemoteAddress()
                    .', cause: Connect wait timeout in '.$this->getConnectTimeout().' ms.');
            }

            Log::info('Connect to server '.$this->getRemoteAddress().' from '.class_basename($this));
        } catch (HomerException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::channel('homer')->warning('Failed to connect to server '.$this->getRemoteAddress(), [
                'exception' => $e
            ]);

            throw new HomerException('Failed to connect to server '.$this->host.':'.$this->port.', cause: '
                .$e->getMessage());
        }
    }

    /**
     * 断开与服务器的连接
     */
    public function disconnect()
    {
        try {
            $this->doDisconnect();
        } catch (Throwable $e) {
            Log::warning($e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    /**
     * 重新连接服务器
     */
    public function reconnect()
    {
        $this->disconnect();

        $this->connect();
    }

    /**
     * 是否已经连接到服务器
     *
     * @return bool
     */
    abstract public function isConnected();

    /**
     * 连接服务器
     */
    abstract protected function doConnect();

    /**
     * 断开与服务器的连接
     */
    abstract protected function doDisconnect();

    /**
     * 发送消息请求
     *
     * @param mixed $message
     *
     * @return mixed
     */
    abstract public function request($message);
}