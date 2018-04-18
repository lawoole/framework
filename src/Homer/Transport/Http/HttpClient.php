<?php
namespace Lawoole\Homer\Transport\Http;

use GuzzleHttp\Client as GuzzleHttpClient;
use Lawoole\Homer\HomerException;
use Lawoole\Homer\Transport\Client;

class HttpClient extends Client
{
    /**
     * Guzzle 客户端
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * 创建 Http 协议客户端
     *
     * @param string $host
     * @param int $port
     * @param array $options
     */
    public function __construct($host, $port, array $options = [])
    {
        parent::__construct($host, $port, $options);
    }

    /**
     * 是否已经连接到服务器
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->client !== null;
    }

    /**
     * 连接服务器
     */
    protected function doConnect()
    {
        $this->client = new GuzzleHttpClient([
            'base_uri'        => "http://{$this->host}:{$this->port}/",
            'timeout'         => $this->getTimeout(),
            'connect_timeout' => $this->getConnectTimeout(),
        ]);
    }

    /**
     * 断开与服务器的连接
     */
    protected function doDisconnect()
    {
        $this->client = null;
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
        $body = serialize($message);

        $response = $this->client->request('POST', '', [
            'expect' => false,
            'body'   => $body
        ]);

        if ($response->getStatusCode() != 200) {
            throw new HomerException($response->getBody()->getContents() ?: 'Http request failed, status'
                .$response->getStatusCode());
        }

        $body = $response->getBody()->getContents();

        return unserialize($body);
    }
}
