<?php
namespace Lawoole\Homer\Transport\Whisper;

use Lawoole\Contracts\Foundation\Application;
use Lawoole\Homer\Dispatcher;
use Lawoole\Homer\Transport\SerializeServerSocketMessages;
use Lawoole\Server\ServerSockets\ServerSocketHandler as BaseServerSocketHandler;
use Throwable;

class WhisperServerSocketHandler extends BaseServerSocketHandler
{
    use SerializeServerSocketMessages;

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

        $this->serializerFactory = $app['homer.factory.serializer'];
    }

    /**
     * 获得默认序列化方式
     *
     * @return string
     */
    protected function getDefaultSerializer()
    {
        return 'swoole';
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
        try {
            $serializer = $this->getSerializer($serverSocket);

            $message = $serializer->unserialize(substr($data, 4));

            $result = $this->dispatcher->handleMessage($message);

            $body = $serializer->serialize($result);

            $this->respond($server, $fd, 200, $body);
        } catch (Throwable $e) {
            $this->respond($server, $fd, 500, $e->getMessage());

            $server->closeConnection($fd);
        }
    }

    /**
     * 发送响应
     *
     * @param \Lawoole\Server\Server $server
     * @param int $fd
     * @param int $status
     * @param string $body
     */
    protected function respond($server, $fd, $status, $body)
    {
        $swooleServer = $server->getSwooleServer();

        $swooleServer->send($fd, pack('n', $status));
        $swooleServer->send($fd, pack('N', strlen($body)));
        $swooleServer->send($fd, $body);
    }
}
