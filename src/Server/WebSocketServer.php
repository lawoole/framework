<?php
namespace Lawoole\Server;

use Lawoole\Server\ServerSockets\ServerSocket;
use Swoole\WebSocket\Server as WebSocketHttpServer;

class WebSocketServer extends HttpServer
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
        'Connect', 'Close', 'Receive', 'Packet', 'Request', 'Open', 'Message'
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
        return new WebSocketHttpServer(
            $serverSocket->getHost(),
            $serverSocket->getPort(),
            $processMode,
            $serverSocket->getSocketType()
        );
    }

    /**
     * 注册事件回调
     */
    protected function registerOpenCallback()
    {
        $this->swooleServer->on('Open', function ($server, $request) {
            $this->serverSocket->dispatchEvent('Open', $this, $this->serverSocket, $request);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerMessageCallback()
    {
        $this->swooleServer->on('Message', function ($server, $frame) {
            $this->serverSocket->dispatchEvent('Message', $this, $this->serverSocket, $frame);
        });
    }
}
