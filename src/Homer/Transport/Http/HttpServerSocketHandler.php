<?php
namespace Lawoole\Homer\Transport\Http;

use Lawoole\Contracts\Foundation\Application;
use Lawoole\Homer\Dispatcher;
use Lawoole\Server\ServerSockets\HttpServerSocketHandler as BaseHttpServerSocketHandler;
use Throwable;

class HttpServerSocketHandler extends BaseHttpServerSocketHandler
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
     * 收到 Http 处理请求时调用
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function onRequest($server, $serverSocket, $request, $response)
    {
        try {
            $message = unserialize($request->rawcontent());

            $result = $this->dispatcher->handleMessage($message);

            $body = serialize($result);

            $this->respond($response, 200, $body);
        } catch (Throwable $e) {
            $this->respond($response, 500, $e->getMessage());
        }
    }

    /**
     * 发送响应
     *
     * @param \Swoole\Http\Response $response
     * @param int $status
     * @param string $body
     */
    protected function respond($response, $status, $body)
    {
        $response->status($status);
        $response->end($body);
    }
}