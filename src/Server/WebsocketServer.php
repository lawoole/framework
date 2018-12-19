<?php
namespace Lawoole\Server;

use Swoole\Websocket\Server as WebsocketHttpServer;

class WebsocketServer extends HttpServer
{
    /**
     * An array of available server events.
     *
     * @var array
     */
    protected $serverEvents = [
        'Start', 'Shutdown', 'ManagerStart', 'ManagerStop',
        'WorkerStart', 'WorkerStop', 'WorkerExit', 'WorkerError',
        'Task', 'Finish', 'PipeMessage', 'BufferFull', 'BufferEmpty',
        'Connect', 'Close', 'Receive', 'Request', 'HandShake', 'Message'
    ];

    /**
     * The handler for hand shaking.
     *
     * @var mixed
     */
    protected $handShaker;

    /**
     * Create the Swoole server instance.
     *
     * @param array $config
     *
     * @return \Swoole\Server
     */
    protected function createSwooleServer(array $config)
    {
        return new WebsocketHttpServer(
            $this->serverSocket->getHost(),
            $this->serverSocket->getPort(),
            $this->parseServerMode($config),
            $this->serverSocket->getSocketType()
        );
    }

    /**
     * Get the hand shaker.
     *
     * @return callable
     */
    public function getHandShaker()
    {
        if ($this->handShaker === null) {
            return $this->getDefaultHandShaker();
        }

        if (! is_callable($this->handShaker)) {
            $this->handShaker = $this->app->make($this->handShaker);

            if (is_object($this->handShaker)) {
                $this->handShaker = [$this->handShaker, 'handle'];
            }
        }

        return $this->handShaker;
    }

    /**
     * Get the default hand shaker.
     *
     * @return \Closure
     */
    protected function getDefaultHandShaker()
    {
        return function ($server, $request, $response) {
            $response->status(101);
            $response->header('Upgrade', 'websocket');
            $response->header('Connection', 'Upgrade');
            $response->header('Sec-Websocket-Accept', $this->getWebsocketAccept($request));
            $response->header('Sec-Websocket-Version', '13');

            // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
            if (isset($request->header['sec-websocket-protocol'])) {
                $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
            }

            $response->end();

            return true;
        };
    }

    /**
     * Get the websocket accept response header.
     *
     * @param \Swoole\Http\Request $request
     *
     * @return string
     */
    protected function getWebsocketAccept($request)
    {
        $websocketKey = $request->header['sec-websocket-key'];
        
        return base64_encode(sha1($websocketKey.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
    }

    /**
     * Set the hand shaker.
     *
     * @param mixed $handShaker
     */
    public function setHandShaker($handShaker)
    {
        $this->handShaker = $handShaker;
    }

    /**
     * Shake hands.
     *
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     *
     * @return bool
     */
    protected function handShake($request, $response)
    {
        $handShaker = $this->getHandShaker();

        return $handShaker($this, $request, $response);
    }

    /**
     * Register the event callback.
     */
    protected function registerHandShakeCallback()
    {
        $this->swooleServer->on('HandShake', function ($server, $request, $response) {
            $this->dispatchEvent('HandShake', $this, $request, $response);

            $accepted = $this->handShake($request, $response);

            if ($accepted) {
                $this->dispatchEvent('Open', $this, $request);
            }
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerMessageCallback()
    {
        $this->swooleServer->on('Message', function ($server, $frame) {
            $this->dispatchEvent('Message', $this, $frame);
        });
    }
}
