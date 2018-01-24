<?php
namespace Lawoole\Swoole;

use RuntimeException;
use Swoole\Server\Port;

class HttpServerSocket extends ServerSocket
{
    /**
     * 服务 Socket 选项
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
    protected $serverSocketEvents = [
        'Close', 'Request', 'BufferFull', 'BufferEmpty'
    ];

    /**
     * 绑定到服务对象
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Swoole\Server\Port $swooleServerPort
     */
    public function bindToServer(Server $server, Port $swooleServerPort)
    {
        if (!$server instanceof HttpServer) {
            throw new RuntimeException('Http server socket must be bound on a Http server.');
        }

        parent::bindToServer($server, $swooleServerPort);
    }

    /**
     * 注册事件回调
     */
    protected function registerRequestCallback()
    {
        $this->swooleServerPort->on('Request', function ($request, $response) {
            $this->dispatchEvent('Request', $this->server, $this, $request, $response);
        });
    }
}
