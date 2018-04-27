<?php
namespace Lawoole\Homer\Transport;

use Illuminate\Support\Facades\Log;
use Lawoole\Contracts\Foundation\Application;
use Lawoole\Homer\HomerException;
use Throwable;

abstract class Client
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * 配置
     *
     * @var array
     */
    protected $config;

    /**
     * 序列化工具
     *
     * @var \Lawoole\Homer\Serialization\Serializers\Serializer
     */
    protected $serializer;

    /**
     * 创建客户端
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     * @param array $config
     */
    public function __construct(Application $app, array $config = [])
    {
        $this->app = $app;
        $this->config = $config;

        $this->serializer = $this->createSerializer();
    }

    /**
     * 创建序列化工具
     *
     * @return \Lawoole\Homer\Serialization\Serializers\Serializer
     */
    protected function createSerializer()
    {
        $factory = $this->app['homer.factory.serializer'];

        return $factory->getSerializer(
            $config['serializer'] ?? $this->getDefaultSerializer()
        );
    }

    /**
     * 获得远端主机名
     *
     * @return string
     */
    public function getHost()
    {
        return $this->config['host'];
    }

    /**
     * 获得远端端口
     *
     * @return int
     */
    public function getPort()
    {
        return $this->config['port'];
    }

    /**
     * 获得服务器地址
     *
     * @return string
     */
    public function getRemoteAddress()
    {
        return "{$this->config['host']}:{$this->config['port']}";
    }

    /**
     * 获得序列化工具
     *
     * @return \Lawoole\Homer\Serialization\Serializers\Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * 获得超时
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->options['timeout'] ?? 5000;
    }

    /**
     * 获得连接失败重试次数
     *
     * @return int
     */
    public function getRetryTimes()
    {
        return $this->options['retry_times'] ?? 1;
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
                $this->disconnect();

                throw new TransportException('Failed to connect to server '.$this->getRemoteAddress()
                    .', cause: Connect timeout '.$this->getTimeout().' ms.', 11);
            }

            Log::channel('homer')->info('Connect to server '.$this->getRemoteAddress().' from '.class_basename($this));
        } catch (TransportException $e) {
            $this->disconnect();

            throw $e;
        } catch (Throwable $e) {
            $this->disconnect();

            Log::channel('homer')->warning('Failed to connect to server '.$this->getRemoteAddress(), [
                'exception' => $e
            ]);

            throw new TransportException('Failed to connect to server '.$this->getRemoteAddress().', cause: '
                .$e->getMessage(), 0, $e);
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
            Log::channel('homer')->warning($e->getMessage(), [
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
     * 发送消息请求
     *
     * @param mixed $message
     *
     * @return mixed
     */
    public function request($message)
    {
        $this->reconnectIfLostConnection();

        try {
            return $this->doRequest($message);
        } catch (HomerException $e) {
            $this->disconnect();

            throw $e;
        } catch (Throwable $e) {
            $this->disconnect();

            Log::channel('homer')->warning('Send rpc request failed', [
                'exception' => $e
            ]);

            throw new TransportException('Send rpc request failed, cause: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * 检查连接
     */
    protected function reconnectIfLostConnection()
    {
        if ($this->isConnected()) {
            return;
        }

        $this->reconnect();
    }

    /**
     * 获得默认序列化方式
     *
     * @return string
     */
    abstract protected function getDefaultSerializer();

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
    abstract protected function doRequest($message);
}
