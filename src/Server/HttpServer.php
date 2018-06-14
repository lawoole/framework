<?php
namespace Lawoole\Server;

use Swoole\Http\Server as SwooleHttpServer;

class HttpServer extends Server
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
        'Connect', 'Close', 'Receive', 'Request'
    ];

    /**
     * Create the Swoole server instance.
     *
     * @return \Swoole\Http\Server
     */
    protected function createSwooleServer()
    {
        return new SwooleHttpServer(
            $this->serverSocket->getHost(),
            $this->serverSocket->getPort(),
            $this->parseServerMode(),
            $this->serverSocket->getSocketType()
        );
    }

    /**
     * Register the event callback.
     */
    protected function registerRequestCallback()
    {
        $this->swooleServer->on('Request', function () {});
    }
}
