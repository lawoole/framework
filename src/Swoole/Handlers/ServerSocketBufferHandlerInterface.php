<?php
namespace Lawoole\Swoole\Handlers;

interface ServerSocketBufferHandlerInterface
{
    /**
     * 当缓存区达到高位线时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param int $fd
     */
    public function onBufferFull($server, $serverSocket, $fd);

    /**
     * 当缓存区降至低位线时调用

     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param int $fd
     */
    public function onBufferEmpty($server, $serverSocket, $fd);
}
