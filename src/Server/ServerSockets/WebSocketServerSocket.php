<?php
namespace Lawoole\Server\ServerSockets;

class WebSocketServerSocket extends HttpServerSocket
{
    /**
     * 配置选项
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
     * 可用事件回调
     *
     * @var array
     */
    protected $serverSocketEvents = [
        'Close', 'Request', 'Open', 'Message', 'BufferFull', 'BufferEmpty'
    ];

    /**
     * 注册事件回调
     */
    protected function registerOpenCallback()
    {
        $this->swoolePort->on('Open', function ($server, $request) {
            $this->dispatchEvent('Open', $this->server, $this, $request);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerMessageCallback()
    {
        $this->swoolePort->on('Message', function ($server, $frame) {
            $this->dispatchEvent('Message', $this->server, $this, $frame);
        });
    }
}
