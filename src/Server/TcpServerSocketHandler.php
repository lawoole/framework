<?php
namespace Lawoole\Server;

use Lawoole\Application;
use Lawoole\Console\OutputStyle;
use Lawoole\Swoole\Handlers\TcpServerSocketHandler as TcpServerSocketHandlerContract;

class TcpServerSocketHandler implements TcpServerSocketHandlerContract
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Application
     */
    protected $app;

    /**
     * 控制台输出样式
     *
     * @var \Lawoole\Console\OutputStyle
     */
    protected $outputStyle;

    /**
     * 创建 Http 服务 Socket 处理器
     *
     * @param \Lawoole\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->outputStyle = $app->make(OutputStyle::class);
    }

    /**
     * 新连接进入时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param int $fd
     * @param int $reactorId
     */
    public function onConnect($server, $serverSocket, $fd, $reactorId)
    {
        $this->outputStyle->info("New connection created, fd: {$fd}, reactor id: {$reactorId}.");
    }

    /**
     * 从连接中取得数据时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     */
    public function onReceive($server, $serverSocket, $fd, $reactorId, $data)
    {
        $this->outputStyle->info("Receive data, fd: {$fd}, reactor id: {$reactorId}, data:");

        $this->outputStyle->line($data);
    }

    /**
     * 当连接关闭时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param int $fd
     * @param int $reactorId
     */
    public function onClose($server, $serverSocket, $fd, $reactorId)
    {
        $this->outputStyle->info("Connection closed, fd: {$fd}, reactor id: {$reactorId}.");
    }
}
