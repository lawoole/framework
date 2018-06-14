<?php
namespace Lawoole\Server\ServerSockets;

class UdpServerSocket extends ServerSocket
{
    /**
     * Get default socket type for this server socket.
     *
     * @return int
     */
    protected function getDefaultSocketType()
    {
        return SWOOLE_SOCK_UDP;
    }
}
