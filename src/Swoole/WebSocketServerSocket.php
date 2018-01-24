<?php
namespace Lawoole\Swoole;

use RuntimeException;
use Swoole\Server\Port;

class WebSocketServerSocket extends HttpServerSocket
{
    /**
     * 服务 Socket 选项
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
     * 绑定到服务对象
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Swoole\Server\Port $swooleServerPort
     */
    public function bindToServer(Server $server, Port $swooleServerPort)
    {
        if (!$server instanceof WebSocketServerSocket) {
            throw new RuntimeException('WebSocket server socket must be bound on a WebSocket server.');
        }

        parent::bindToServer($server, $swooleServerPort);
    }


    /**
     * 注册事件回调
     */
    protected function registerOpenCallback()
    {
        $this->swooleServerPort->on('Open', function ($server, $request) {
            $this->dispatchEvent('Open', $this->server, $this, $request);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerMessageCallback()
    {
        $this->swooleServerPort->on('Message', function ($server, $frame) {
            $this->dispatchEvent('Message', $this->server, $this, $frame);
        });
    }
}
