<?php
namespace Lawoole\Swoole;

use Swoole\WebSocket\Server as SwooleWebSocketServer;

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
     * @return \Swoole\WebSocket\Server
     */
    protected function createSwooleServer()
    {
        return new SwooleWebSocketServer($this->getUnixSock(), 0, SWOOLE_PROCESS, SWOOLE_SOCK_UNIX_STREAM);
    }

    /**
     * 注册事件回调
     */
    protected function registerOpenCallback()
    {
        $this->swooleServer->on('Open', function ($server, $request) {
            $this->dispatchEvent('Open', $this, $this->serverSocket, $request);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerMessageCallback()
    {
        $this->swooleServer->on('Message', function ($server, $frame) {
            $this->dispatchEvent('Message', $this, $this->serverSocket, $frame);
        });
    }
}
