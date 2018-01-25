<?php
namespace Lawoole\Swoole\Handlers;

interface ServerSocketHandler
{
    /**
     * 在服务 Socket 绑定到服务时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     */
    public function onBind($server, $serverSocket);
    
    /**
     * 在服务即将启动时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     */
    public function onLaunch($server, $serverSocket);
}
