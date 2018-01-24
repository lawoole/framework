<?php
namespace Lawoole\Swoole\Handlers;

interface UdpServerSocketHandler
{
    /**
     * 接收到 UDP 包时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param string $data
     * @param array $clientInfo
     */
    public function onPacket($server, $serverSocket, $data, $clientInfo);
}
