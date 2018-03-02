<?php
namespace Lawoole\Server;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Lawoole\Console\OutputStyle;
use Lawoole\Contracts\Foundation\Application;
use Lawoole\Contracts\Server\Server as ServerContract;
use Lawoole\Contracts\Server\ServerSocket;
use Lawoole\Server\Concerns\HasEventHandler;
use Swoole\Server as SwooleServer;

class Server implements ServerContract
{
    use HasEventHandler;

    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * 控制台输出
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * Swoole 服务对象
     *
     * @var \Swoole\Server
     */
    protected $swooleServer;

    /**
     * 创建服务对象
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->output = $app['console.output'];

        $this->outputStyle = new OutputStyle($input, $output);
    }

    /**
     * 创建 Swoole 服务
     *
     * @param array $config
     *
     * @return \Swoole\Server
     */
    protected function createSwooleServer(array $config)
    {
        return new SwooleServer($this->getUnixSock(), 0, SWOOLE_PROCESS, SWOOLE_SOCK_UNIX_STREAM);
    }

    /**
     * 获得 Swoole 服务对象
     *
     * @return \Swoole\Server
     */
    public function getSwooleServer()
    {
        return $this->swooleServer;
    }

    /**
     * 添加监听定义
     *
     * @param \Lawoole\Contracts\Server\ServerSocket $serverSocket
     */
    public function listen(ServerSocket $serverSocket)
    {
        // TODO: Implement listen() method.
    }

    /**
     * 判断服务是否正在运行
     *
     * @return bool
     */
    public function isServing()
    {
        // TODO: Implement isServing() method.
    }

    /**
     * 启动服务
     */
    public function serve()
    {
        // TODO: Implement serve() method.
    }

    /**
     * 停止服务
     */
    public function shutdown()
    {
        // TODO: Implement shutdown() method.
    }

    /**
     * 获得所有当前连接的迭代器
     *
     * @return \Iterator
     */
    public function getConnectionIterator()
    {
        // TODO: Implement getConnectionIterator() method.
    }

    /**
     * 处理异常
     *
     * @param \Exception $e
     */
    protected function handleException(Exception $e)
    {
        $handler = $this->app->make(ExceptionHandler::class);

        $handler->report($e);

        $output = $this->app->make(OutputInterface::class);

        $handler->renderForConsole($output, $e);
    }
}
