<?php
namespace Lawoole\Swoole;

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
     * @return \Swoole\Http\Server
     */
    protected function createSwooleServer()
    {
        return new SwooleHttpServer($this->getUnixSock(), 0, SWOOLE_PROCESS, SWOOLE_SOCK_UNIX_STREAM);
    }

    /**
     * 注册事件回调
     */
    protected function registerRequestCallback()
    {
        $this->swooleServer->on('Request', function ($request, $response) {
            $this->dispatchEvent('Request', $this, $this->serverSocket, $request, $response);
        });
    }
}
