<?php
namespace Lawoole\Homer\Transport;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Lawoole\Homer\HomerException;
use Lawoole\Homer\Serialize\Factory as SerializerFactory;
use Throwable;

abstract class Client
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The client configurations.
     *
     * @var array
     */
    protected $config;

    /**
     * The data serializer.
     *
     * @var \Lawoole\Homer\Serialize\Serializer
     */
    protected $serializer;

    /**
     * Create a client instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Lawoole\Homer\Serialize\Factory $serializerFactory
     * @param array $config
     */
    public function __construct(Application $app, SerializerFactory $serializerFactory, array $config = [])
    {
        $this->app = $app;
        $this->config = $config;

        $this->serializer = $serializerFactory->serializer(
            $config['serializer'] ?? $this->getDefaultSerializer()
        );
    }

    /**
     * Get the host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->config['host'];
    }

    /**
     * Get the remote port.
     *
     * @return int
     */
    public function getPort()
    {
        return $this->config['port'];
    }

    /**
     * Get the server address.
     *
     * @return string
     */
    public function getRemoteAddress()
    {
        return "{$this->config['host']}:{$this->config['port']}";
    }

    /**
     * Get receive timeout.
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->options['timeout'] ?? 5000;
    }

    /**
     * Get the retry times of calling request.
     *
     * @return int
     */
    public function getRetryTimes()
    {
        return $this->options['retry_times'] ?? 0;
    }

    /**
     * Connect to the server.
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

            Log::channel('homer')->warning($e->getMessage(), [
                'exception' => $e
            ]);

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
     * Disconnect to the server.
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
     * Reconnect to the server.
     */
    public function reconnect()
    {
        $this->disconnect();

        $this->connect();
    }

    /**
     * Send calling request.
     *
     * @param mixed $message
     *
     * @return mixed
     */
    public function request($message)
    {
        $this->reconnectIfLostConnection();

        $retryTimes = $this->getRetryTimes();

        try {
            $body = $this->serializer->serialize($message);

            do {
                try {
                    $data = $this->doRequest($body);

                    break;
                } catch (TransportException $e) {
                    if ($e->isConnection() && $retryTimes-- > 0) {
                        continue;
                    }

                    throw $e;
                }
            } while ($retryTimes > 0);

            return $this->serializer->unserialize($data);
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
     * Check the connection.
     */
    protected function reconnectIfLostConnection()
    {
        if ($this->isConnected()) {
            return;
        }

        $this->reconnect();
    }

    /**
     * Get default serialize driver used in the client.
     *
     * @return string
     */
    abstract protected function getDefaultSerializer();

    /**
     * Return whether the client has connected to the server.
     *
     * @return bool
     */
    abstract public function isConnected();

    /**
     * Connect to the server.
     */
    abstract protected function doConnect();

    /**
     * Disconnect to the server.
     */
    abstract protected function doDisconnect();

    /**
     * Send calling request.
     *
     * @param string $data
     *
     * @return string
     */
    abstract protected function doRequest($data);
}
