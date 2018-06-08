<?php
namespace Lawoole\Homer\Transport\Http;

use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Str;
use Lawoole\Homer\Transport\Client;
use Lawoole\Homer\Transport\TransportException;
use Throwable;

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
            'base_uri' => "http://{$this->getRemoteAddress()}/",
            'timeout'  => $this->getTimeout() / 1000.0,
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
     * @param string $data
     *
     * @return string
     */
    protected function doRequest($data)
    {
        try {
            $response = $this->client->request('POST', '', [
                'expect' => false,
                'body'   => $data
            ]);

            if ($response->getStatusCode() != 200) {
                throw new TransportException($response->getBody()->getContents() ?: 'Http request failed, status: '
                    .$response->getStatusCode(), TransportException::REMOTE);
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

        return $response->getBody()->getContents();
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
            'error 6: Couldn\'t resolve host',
            'error 7: couldn\'t connect to host',
        ]);
    }
}
