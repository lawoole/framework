<?php
namespace Lawoole\Swoole\Handlers;

interface TcpServerSocketHandlerInterface extends ServerSocketHandlerInterface
{
    /**
     * 新连接进入时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param int $fd
     * @param int $reactorId
     */
    public function onConnect($server, $serverSocket, $fd, $reactorId);

    /**
     * 从连接中取得数据时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     */
    public function onReceive($server, $serverSocket, $fd, $reactorId, $data);

    /**
     * 当连接关闭时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param int $fd
     * @param int $reactorId
     */
    public function onClose($server, $serverSocket, $fd, $reactorId);
}
