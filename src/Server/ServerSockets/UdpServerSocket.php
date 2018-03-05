<?php
namespace Lawoole\Server\ServerSockets;

class UdpServerSocket extends ServerSocket
{
    /**
     * 获得默认的 Socket 类型
     *
     * @return int
     */
    protected function getDefaultSocketType()
    {
        return SWOOLE_SOCK_UDP;
    }
}
