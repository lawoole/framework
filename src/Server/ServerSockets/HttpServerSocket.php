<?php
namespace Lawoole\Server\ServerSockets;

class HttpServerSocket extends ServerSocket
{
    /**
     * 配置选项
     *
     * @var array
     */
    protected $options = [
        'tcp_fastopen'       => true,
        'open_tcp_nodelay'   => true,
        'open_http_protocol' => true
    ];

    /**
     * 可用事件回调
     *
     * @var array
     */
    protected $serverEvents = [
        'Close', 'Request', 'BufferFull', 'BufferEmpty'
    ];

    /**
     * 注册事件回调
     */
    protected function registerRequestCallback()
    {
        $this->swoolePort->on('Request', function ($request, $response) {
            $this->dispatchEvent('Request', $this->server, $this, $request, $response);
        });
    }
}
