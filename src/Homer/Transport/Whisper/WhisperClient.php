<?php
namespace Lawoole\Homer\Transport\Whisper;

use Lawoole\Homer\HomerException;
use Lawoole\Homer\Transport\Client;
use Swoole\Client as SwooleClient;
use Swoole\Serialize;
use Throwable;

class WhisperClient extends Client
{
    /**
     * Swoole 客户端
     *
     * @var \Swoole\Client
     */
    protected $client;

    /**
     * 创建 Whisper 协议客户端
     *
     * @param string $host
     * @param int $port
     * @param array $options
     */
    public function __construct($host, $port, array $options = [])
    {
        parent::__construct($host, $port, $options);

        $this->client = new SwooleClient(SWOOLE_TCP, SWOOLE_SOCK_SYNC);

        $this->client->set([
            'open_length_check'     => true,
            'package_length_type'   => 'N',
            'package_max_length'    => 5120000,
            'package_length_offset' => 0,
            'package_body_offset'   => 4,
        ]);
    }

    /**
     * 是否已经连接到服务器
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->client->isConnected();
    }

    /**
     * 连接服务器
     */
    protected function doConnect()
    {
        $result = $this->client->connect($this->host, $this->port, $this->getConnectTimeout() / 1000.0);

        if ($result == false) {
            $errorCode = $this->client->errCode;

            if ($errorCode == 99) {
                throw new HomerException('Cannot open a socket to connect server.');
            }

            throw new HomerException('Connect to server failed, cause: '.socket_strerror($errorCode).'.');
        }
    }

    /**
     * 断开与服务器的连接
     */
    protected function doDisconnect()
    {
        $this->client->close(true);
    }

    /**
     * 发送消息请求
     *
     * @param mixed $message
     *
     * @return mixed
     */
    protected function doRequest($message)
    {
        try {
            $body = Serialize::pack($message);

            $this->send(pack('N', strlen($body)));
            $this->send($body);

            $data = $this->receive();

            return $message = Serialize::unpack(substr($data, 4));
        } catch (Throwable $e) {
            $this->disconnect();

            throw $e;
        }
    }

    /**
     * 发送数据
     *
     * @param string $data
     *
     * @return int
     */
    protected function send($data)
    {
        try {
            $result = $this->client->send($data);

            if ($result === false) {
                $errorCode = $this->client->errCode;

                throw new HomerException('Send data failed, cause: '.socket_strerror($errorCode).'.');
            }
        } catch (HomerException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new HomerException($e->getMessage(), $e);
        }

        return $result;
    }

    /**
     * 接收消息
     *
     * @return string
     */
    protected function receive()
    {
        try {
            $data = $this->client->recv();

            if ($data === false) {
                $errorCode = $this->client->errCode;

                if ($errorCode == 11) {
                    throw new HomerException('Receive timeout in '.$this->getTimeout().' ms.');
                }

                throw new HomerException('Receive data failed, cause: '.socket_strerror($errorCode).'.');
            } elseif ($data === '') {
                throw new HomerException('Receive data failed, cause the connection has been closed.');
            }
        } catch (HomerException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new HomerException($e->getMessage(), $e);
        }

        return $data;
    }
}
