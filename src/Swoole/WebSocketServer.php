<?php
namespace Lawoole\Swoole;

use Swoole\WebSocket\Server as SwooleWebSocketServer;

class WebSocketServer extends HttpServer
{
    /**
     * 创建 Swoole 服务
     *
     * @return \Swoole\WebSocket\Server
     */
    protected function createSwooleServer()
    {
        return new SwooleWebSocketServer($this->getUnixSock(), 0, SWOOLE_PROCESS, SWOOLE_SOCK_UNIX_STREAM);
    }
}
