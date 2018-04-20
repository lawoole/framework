<?php
namespace Lawoole\Homer\Transport\Http;

use GuzzleHttp\Client as GuzzleHttpClient;
use Lawoole\Homer\Transport\Client;
use Lawoole\Homer\Transport\TransportException;

class HttpClient extends Client
{
    /**
     * Guzzle 客户端
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * 获得默认序列化方式
     *
     * @return string
     */
    protected function getDefaultSerializer()
    {
        return 'php';
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
            'base_uri'        => "http://{$this->getRemoteAddress()}/",
            'timeout'         => $this->getTimeout(),
            'connect_timeout' => $this->getTimeout(),
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
        $body = $this->serializer->serialize($message);

        $response = $this->client->request('POST', '', [
            'expect' => false,
            'body'   => $body
        ]);

        if ($response->getStatusCode() != 200) {
            throw new TransportException($response->getBody()->getContents() ?: 'Http request failed, status: '
                .$response->getStatusCode());
        }

        $body = $response->getBody()->getContents();

        return $this->serializer->unserialize($body);
    }
}
