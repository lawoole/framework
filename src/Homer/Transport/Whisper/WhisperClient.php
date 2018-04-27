<?php
namespace Lawoole\Homer\Transport\Whisper;

use Illuminate\Support\Str;
use Lawoole\Homer\Transport\Client;
use Lawoole\Homer\Transport\TransportException;
use Swoole\Client as SwooleClient;
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
     * 获得默认序列化方式
     *
     * @return string
     */
    protected function getDefaultSerializer()
    {
        return 'swoole';
    }

    /**
     * 是否已经连接到服务器
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->client !== null && $this->client->isConnected();
    }

    /**
     * 连接服务器
     */
    protected function doConnect()
    {
        try {
            $this->client = new SwooleClient(SWOOLE_TCP, SWOOLE_SOCK_SYNC);

            $this->client->set([
                'open_length_check'     => true,
                'package_length_type'   => 'N',
                'package_max_length'    => 5120000,
                'package_length_offset' => 2,
                'package_body_offset'   => 6,
            ]);

            $result = $this->client->connect($this->getHost(), $this->getPort(), $this->getTimeout() / 1000.0);

            if ($result == false) {
                throw new TransportException('Connect to server ['.$this->getRemoteAddress().'] failed, cause: '
                    .socket_strerror($this->client->errCode).'.');
            }
        } catch (TransportException $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($this->causedByConnectionProblem($e)) {
                $this->disconnect();

                throw new TransportException($e->getMessage(), TransportException::CONNECTION, $e);
            }

            throw new TransportException($e->getMessage(), 0, $e);
        }
    }

    /**
     * 断开与服务器的连接
     */
    protected function doDisconnect()
    {
        if ($this->client) {
            $this->client->close(true);
        }

        $this->client = null;
    }

    /**
     * 发送消息请求
     *
     * @param string $data
     *
     * @return string
     */
    protected function doRequest($data)
    {
        try {
            $this->send(pack('N', strlen($data)).$data);

            $data = $this->receive();

            $status = unpack('nstatus', substr($data, 0, 2))['status'];
            $data = substr($data, 6);

            if ($status != 200) {
                throw new TransportException($data ?: 'Http request failed, status: '.$status,
                    TransportException::REMOTE);
            }

            return $data;
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

                throw new TransportException('Send data failed, cause: '.socket_strerror($errorCode).'.', $errorCode);
            }
        } catch (Throwable $e) {
            if ($this->causedByConnectionProblem($e)) {
                $this->disconnect();

                throw new TransportException($e->getMessage(), TransportException::CONNECTION, $e);
            }

            throw new TransportException($e->getMessage(), 0, $e);
        }

        return $result;
    }

    /**
     * 判断异常是否由连接问题引发
     *
     * @param \Throwable $e
     *
     * @return mixed
     */
    protected function causedByConnectionProblem(Throwable $e)
    {
        $message = $e->getMessage();

        return Str::contains($message, [
            'Broken pipe[32]',
            'Connection reset by peer[104]',
            'Connection refused[111]',
        ]);
    }

    /**
     * 判断异常是否由超时引发
     *
     * @param \Throwable $e
     *
     * @return mixed
     */
    protected function causedByTimeout(Throwable $e)
    {
        $message = $e->getMessage();

        return Str::contains($message, [
            'Resource temporarily unavailable [11]',
        ]);
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
                    throw new TransportException('Receive timeout in '.$this->getTimeout().' ms.',
                        TransportException::TIMEOUT);
                }

                throw new TransportException('Receive data failed, cause: '.socket_strerror($errorCode).'.',
                    $errorCode);
            } elseif ($data === '') {
                throw new TransportException('Receive data failed, cause the connection has been closed.',
                    TransportException::CONNECTION);
            }
        } catch (TransportException $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($this->causedByTimeout($e)) {
                $this->disconnect();

                throw new TransportException('Receive timeout in '.$this->getTimeout().' ms.',
                    TransportException::TIMEOUT, $e);
            }

            throw new TransportException($e->getMessage(), 0, $e);
        }

        return $data;
    }
}
