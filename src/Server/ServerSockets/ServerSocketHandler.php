<?php
namespace Lawoole\Server\ServerSockets;

class ServerSocketHandler
{
    /**
     * 在服务 Socket 绑定到服务时调用
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     */
    public function onBind($server, $serverSocket)
    {
    }

    /**
     * 在服务 Socket 即将暴露调用
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     */
    public function onExport($server, $serverSocket)
    {
    }

    /**
     * 新连接进入时调用
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param int $fd
     * @param int $reactorId
     */
    public function onConnect($server, $serverSocket, $fd, $reactorId)
    {
    }

    /**
     * 从连接中取得数据时调用
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     */
    public function onReceive($server, $serverSocket, $fd, $reactorId, $data)
    {
    }

    /**
     * 当连接关闭时调用
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param int $fd
     * @param int $reactorId
     */
    public function onClose($server, $serverSocket, $fd, $reactorId)
    {
    }

    /**
     * 当缓存区达到高位线时调用
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param int $fd
     */
    public function onBufferFull($server, $serverSocket, $fd)
    {
    }

    /**
     * 当缓存区降至低位线时调用

     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param int $fd
     */
    public function onBufferEmpty($server, $serverSocket, $fd)
    {
    }
}
