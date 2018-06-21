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
     * The Swoole client instance.
     *
     * @var \Swoole\Client
     */
    protected $client;

    /**
     * Get default serialize type.
     *
     * @return string
     */
    protected function getDefaultSerializer()
    {
        return 'swoole';
    }

    /**
     * Return whether the client has connected to the server.
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->client !== null && $this->client->isConnected();
    }

    /**
     * Connect to the server.
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
     * Disconnect to the server.
     */
    protected function doDisconnect()
    {
        if ($this->client) {
            $this->client->close(true);
        }

        $this->client = null;
    }

    /**
     * Send calling request.
     *
     * @param string $data
     *
     * @return string
     */
    protected function doRequest($data)
    {
        $this->send(pack('N', strlen($data)).$data);

        $data = $this->receive();

        $status = unpack('nstatus', substr($data, 0, 2))['status'];
        $data = substr($data, 6);

        if ($status != 200) {
            throw new TransportException($data ?: 'Http request failed, status: '.$status,
                TransportException::REMOTE);
        }

        return $data;
    }

    /**
     * Send data.
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

                throw new TransportException('Send data failed, cause: '.socket_strerror($errorCode).'.',
                    TransportException::CONNECTION);
            }
        }  catch (TransportException $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($this->causedByConnectionProblem($e)) {
                throw new TransportException($e->getMessage(), TransportException::CONNECTION, $e);
            }

            throw new TransportException($e->getMessage(), 0, $e);
        }

        return $result;
    }

    /**
     * Return whether the exception caused by connection.
     *
     * @param \Throwable $e
     *
     * @return mixed
     */
    protected function causedByConnectionProblem(Throwable $e)
    {
        $message = $e->getMessage();

        return Str::contains($message, [
            'Broken pipe',
            'Connection reset by peer',
            'Connection refused',
        ]);
    }

    /**
     * Return whether the exception caused by network timeout.
     *
     * @param \Throwable $e
     *
     * @return mixed
     */
    protected function causedByTimeout(Throwable $e)
    {
        $message = $e->getMessage();

        return Str::contains($message, [
            'Resource temporarily unavailable',
        ]);
    }

    /**
     * Receive result.
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
                    TransportException::CONNECTION);
            } elseif ($data === '') {
                throw new TransportException('Receive data failed, cause the connection has been closed.',
                    TransportException::CONNECTION);
            }
        } catch (TransportException $e) {
            throw $e;
        } catch (Throwable $e) {
            if ($this->causedByTimeout($e)) {
                throw new TransportException('Receive timeout in '.$this->getTimeout().' ms.',
                    TransportException::TIMEOUT, $e);
            }

            throw new TransportException($e->getMessage(), 0, $e);
        }

        return $data;
    }
}
