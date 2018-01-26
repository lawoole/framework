<?php
namespace Lawoole\Swoole\Handlers;

interface HttpServerSocketHandlerInterface extends ServerSocketHandlerInterface
{
    /**
     * 收到 Http 处理请求时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function onRequest($server, $serverSocket, $request, $response);
}
