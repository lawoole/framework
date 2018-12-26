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
        'Connect', 'Close', 'Receive', 'Request', 'HandShake', 'Message'
    ];

    /**
     * Create the Swoole server instance.
     *
     * @param array $config
     *
     * @return \Swoole\Server
     */
    protected function createSwooleServer(array $config)
    {
        return new WebSocketHttpServer(
            $this->serverSocket->getHost(),
            $this->serverSocket->getPort(),
            $this->parseServerMode($config),
            $this->serverSocket->getSocketType()
        );
    }

    /**
     * Register the event callback.
     */
    protected function registerHandShakeCallback()
    {
        $this->swooleServer->on('HandShake', function ($server, $request, $response) {
            $this->dispatchEvent('HandShake', $this, $request, $response);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerMessageCallback()
    {
        $this->swooleServer->on('Message', function ($server, $frame) {
            $this->dispatchEvent('Message', $this, $frame);
        });
    }
}

