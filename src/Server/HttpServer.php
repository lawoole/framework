<?php
namespace Lawoole\Server;

use Lawoole\Server\ServerSockets\ServerSocket;
use Swoole\Http\Server as SwooleHttpServer;

class HttpServer extends Server
{
    /**
     * 可用事件回调
     *
     * @var array
     */
    protected $serverEvents = [
        'Start', 'Shutdown', 'ManagerStart', 'ManagerStop',
        'WorkerStart', 'WorkerStop', 'WorkerExit', 'WorkerError',
        'Task', 'Finish', 'PipeMessage', 'BufferFull', 'BufferEmpty',
        'Connect', 'Close', 'Receive', 'Packet', 'Request'
    ];

    /**
     * 创建 Swoole 服务
     *
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param int $processMode
     *
     * @return \Swoole\Server
     */
    protected function createSwooleServer(ServerSocket $serverSocket, $processMode)
    {
        return new SwooleHttpServer(
            $serverSocket->getHost(),
            $serverSocket->getPort(),
            $processMode,
            $serverSocket->getSocketType()
        );
    }

    /**
     * 注册事件回调
     */
    protected function registerRequestCallback()
    {
        $this->swooleServer->on('Request', function ($request, $response) {
            $this->serverSocket->dispatchEvent('Request', $this, $this->serverSocket, $request, $response);
        });
    }
}
