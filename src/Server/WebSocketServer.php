<?php
namespace Lawoole\Server;

use Swoole\WebSocket\Server as WebSocketHttpServer;

class WebSocketServer extends HttpServer
{
    /**
     * An array of available server events.
     *
     * @var array
     */
    protected $serverEvents = [
        'Start', 'Shutdown', 'ManagerStart', 'ManagerStop',
        'WorkerStart', 'WorkerStop', 'WorkerExit', 'WorkerError',
        'Task', 'Finish', 'PipeMessage', 'BufferFull', 'BufferEmpty',
        'Connect', 'Close', 'Receive', 'Request', 'Open', 'Message'
    ];

    /**
     * Create the Swoole server instance.
     *
     * @return \Swoole\WebSocket\Server
     */
    protected function createSwooleServer()
    {
        return new WebSocketHttpServer(
            $this->serverSocket->getHost(),
            $this->serverSocket->getPort(),
            $this->parseServerMode(),
            $this->serverSocket->getSocketType()
        );
    }

    /**
     * Register the event callback.
     */
    protected function registerOpenCallback()
    {
        $this->swooleServer->on('Open', function () {});
    }

    /**
     * Register the event callback.
     */
    protected function registerMessageCallback()
    {
        $this->swooleServer->on('Message', function () {});
    }
}
