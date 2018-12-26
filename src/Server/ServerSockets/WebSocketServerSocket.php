<?php
namespace Lawoole\Server\ServerSockets;

class WebSocketServerSocket extends HttpServerSocket
{
    /**
     * The server socket options.
     *
     * @var array
     */
    protected $options = [
        'tcp_fastopen'            => true,
        'open_tcp_nodelay'        => true,
        'open_http_protocol'      => true,
        'open_websocket_protocol' => true
    ];

    /**
     * The array of available commands.
     *
     * @var array
     */
    protected $serverSocketEvents = [
        'Close', 'Request', 'HandShake', 'Message', 'BufferFull', 'BufferEmpty'
    ];

    /**
     * Register the event callback.
     */
    protected function registerHandShakeCallback()
    {
        $this->swoolePort->on('HandShake', function ($server, $request, $response) {
            $this->dispatchEvent('HandShake', $this->server, $this, $request, $response);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerMessageCallback()
    {
        $this->swoolePort->on('Message', function ($server, $frame) {
            $this->dispatchEvent('Message', $this->server, $this, $frame);
        });
    }
}
