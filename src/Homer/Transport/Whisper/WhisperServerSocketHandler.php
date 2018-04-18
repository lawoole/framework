<?php
namespace Lawoole\Homer\Transport\Whisper;

use Lawoole\Contracts\Foundation\Application;
use Lawoole\Homer\Dispatcher;
use Lawoole\Server\ServerSockets\ServerSocketHandler as BaseServerSocketHandler;
use Throwable;

class WhisperServerSocketHandler extends BaseServerSocketHandler
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * 消息调度器
     *
     * @var \Lawoole\Homer\Dispatcher
     */
    protected $dispatcher;

    /**
     * 创建 Http 协议处理器
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     * @param \Lawoole\Homer\Dispatcher $dispatcher
     */
    public function __construct(Application $app, Dispatcher $dispatcher)
    {
        $this->app = $app;
        $this->dispatcher = $dispatcher;
    }

    /**
     * 从连接中取得数据时调用
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     */
    public function onReceive($server, $serverSocket, $fd, $reactorId, $data)
    {
        $swooleServer = $server->getSwooleServer();

        try {
            $message = unserialize(substr($data, 4));

            $result = $this->dispatcher->handleMessage($message);

            $body = serialize($result);

            $swooleServer->send($fd, pack('N', strlen($body)));
            $swooleServer->send($fd, $body);
        } catch (Throwable $e) {
            $swooleServer->close($fd, true);
        }
    }
}
