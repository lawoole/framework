<?php
namespace Lawoole\Server\ServerSockets;

class HttpServerSocket extends ServerSocket
{
    /**
     * The server socket options.
     *
     * @var array
     */
    protected $options = [
        'tcp_fastopen'       => true,
        'open_tcp_nodelay'   => true,
        'open_http_protocol' => true
    ];

    /**
     * The array of available commands.
     *
     * @var array
     */
    protected $serverEvents = [
        'Close', 'Request', 'BufferFull', 'BufferEmpty'
    ];

    /**
     * Register the event callback.
     */
    protected function registerRequestCallback()
    {
        $this->swoolePort->on('Request', function ($request, $response) {
            $this->dispatchEvent('Request', $this->server, $this, $request, $response);
        });
    }
}
