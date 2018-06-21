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
     * The GuzzleHttp client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Get default serialize type.
     *
     * @return string
     */
    protected function getDefaultSerializer()
    {
        return 'php';
    }

    /**
     * Return whether the client has connected to the server.
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->client !== null;
    }

    /**
     * Connect to the server.
     */
    protected function doConnect()
    {
        $this->client = new GuzzleHttpClient([
            'base_uri' => "http://{$this->getRemoteAddress()}/",
            'timeout'  => $this->getTimeout() / 1000.0,
        ]);
    }

    /**
     * Disconnect to the server.
     */
    protected function doDisconnect()
    {
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
                throw new TransportException($e->getMessage(), TransportException::CONNECTION, $e);
            }

            throw new TransportException($e->getMessage(), 0, $e);
        }

        return $response->getBody()->getContents();
    }

    /**
     *  Return whether the exception caused by connection.
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
