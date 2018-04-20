<?php
namespace Lawoole\Server;

use EmptyIterator;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use IteratorAggregate;
use Lawoole\Console\OutputStyle;
use Lawoole\Contracts\Foundation\Application;
use Lawoole\Contracts\Server\Process;
use Lawoole\Contracts\Server\Server as ServerContract;
use Lawoole\Contracts\Server\ServerSocket as ServerSocketContract;
use Lawoole\Server\ServerSockets\ServerSocket;
use RuntimeException;
use Swoole\Server as SwooleServer;

class Server implements ServerContract, IteratorAggregate
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * 事件总线
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * 控制台输出
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * 控制台输出样式
     *
     * @var \Lawoole\Console\OutputStyle
     */
    protected $outputStyle;

    /**
     * Swoole 服务对象
     *
     * @var \Swoole\Server
     */
    protected $swooleServer;

    /**
     * 服务是否正在运行
     *
     * @var bool
     */
    protected $serving = false;

    /**
     * 默认服务 Socket
     *
     * @var \Lawoole\Server\ServerSockets\ServerSocket
     */
    protected $serverSocket;

    /**
     * 服务 Socket 集合
     *
     * @var \Lawoole\Server\ServerSockets\ServerSocket[]
     */
    protected $serverSockets = [];

    /**
     * 配置选项
     *
     * @var array
     */
    protected $options = [
        'reload_async' => true
    ];

    /**
     * 可用事件回调
     *
     * @var array
     */
    protected $serverEvents = [
        'Start', 'Shutdown', 'ManagerStart', 'ManagerStop',
        'WorkerStart', 'WorkerStop', 'WorkerExit', 'WorkerError',
        'Task', 'Finish', 'PipeMessage', 'BufferFull', 'BufferEmpty',
        'Connect', 'Close', 'Receive', 'Packet'
    ];

    /**
     * 创建服务对象
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param int $processMode
     */
    public function __construct(Application $app, ServerSocket $serverSocket, $processMode)
    {
        $this->app = $app;
        $this->events = $app['events'];
        $this->output = $app['console.output'];
        $this->outputStyle = $app[OutputStyle::class];

        $this->serverSocket = $serverSocket;

        $this->swooleServer = $this->createSwooleServer($serverSocket, $processMode);

        $this->configureDefaultServerSocket();

        $this->registerEventCallbacks($this->serverEvents);

        $this->events->dispatch(new Events\ServerCreated($this));
    }

    /**
     * 获得事件分发总线
     *
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    public function getEventDispatcher()
    {
        return $this->events;
    }

    /**
     * 创建 Swoole 服务
     *
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param int $processMode
     *
     * @return \Swoole\Server
     */
    protected function createSwooleServer(ServerSocket $serverSocket, $processMode)
    {
        return new SwooleServer(
            $serverSocket->getHost(),
            $serverSocket->getPort(),
            $processMode,
            $serverSocket->getSocketType()
        );
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
     * 配置默认监听
     */
    protected function configureDefaultServerSocket()
    {
        $this->serverSocket->listen($this, head($this->swooleServer->ports));
    }

    /**
     * 设置配置选项
     *
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        if ($this->serving) {
            throw new RuntimeException('Options cannot be set while the server is serving.');
        }

        $this->options = array_diff_key($this->options, $options) + $options;

        $this->swooleServer->set($this->options);
    }

    /**
     * 获得配置选项
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * 添加监听定义
     *
     * @param \Lawoole\Contracts\Server\ServerSocket $serverSocket
     */
    public function listen(ServerSocketContract $serverSocket)
    {
        if ($this->serving) {
            throw new RuntimeException('Cannot add listening while the server is serving.');
        }

        if ($serverSocket->isBound()) {
            throw new RuntimeException('ServerSocket can be bound to server only once.');
        }

        $swoolePort = $this->swooleServer->listen(
            $host = $serverSocket->getHost(),
            $port = $serverSocket->getPort(),
            $serverSocket->getSocketType()
        );

        $address = $host.($port ? ':'.$port : '');

        $this->serverSockets[$address] = $serverSocket;

        $serverSocket->listen($this, $swoolePort);
    }

    /**
     * 添加子进程
     *
     * @param \Lawoole\Contracts\Server\Process $process
     */
    public function fork(Process $process)
    {
        $this->swooleServer->addProcess($process->getSwooleProcess());

        $process->bind($this);
    }

    /**
     * 判断服务是否正在运行
     *
     * @return bool
     */
    public function isServing()
    {
        return $this->serving;
    }

    /**
     * 启动服务
     */
    public function serve()
    {
        $this->events->dispatch(new Events\ServerLaunching($this));

        $this->serving = true;

        $this->swooleServer->start();

        $this->serving = false;
    }

    /**
     * 停止服务
     */
    public function shutdown()
    {
        if ($this->serving == false) {
            return;
        }

        $this->swooleServer->shutdown();
    }

    /**
     * 重启所有工作进程
     *
     * @param bool $onlyTask
     *
     * @return bool
     */
    public function reload($onlyTask = false)
    {
        return $this->swooleServer->reload($onlyTask);
    }

    /**
     * 获得所有当前连接的迭代器
     *
     * @return \Iterator
     */
    public function getConnectionIterator()
    {
        if ($this->serving) {
            return $this->swooleServer->connections;
        }

        return new EmptyIterator;
    }

    /**
     * 获得所有当前连接的迭代器
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        return $this->getConnectionIterator();
    }

    /**
     * 关闭连接
     *
     * @param int $fd
     * @param bool $force
     *
     * @return bool
     */
    public function closeConnection($fd, $force = false)
    {
        return $this->swooleServer->close($fd, $force);
    }

    /**
     * 注册事件回调
     *
     * @param array $events
     */
    protected function registerEventCallbacks(array $events)
    {
        foreach ($events as $event) {
            call_user_func([$this, "register{$event}Callback"]);
        }
    }

    /**
     * 注册事件回调
     */
    protected function registerStartCallback()
    {
        $this->swooleServer->on('Start', function () {
            $name = $this->app->name();

            $this->outputStyle->info("{$name} server is running.");

            if (php_uname('s') != 'Darwin') {
                swoole_set_process_name("{$name} : Master");
            }

            $this->events->dispatch(new Events\ServerStarted($this));
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerShutdownCallback()
    {
        $this->swooleServer->on('Shutdown', function () {
            $this->events->dispatch(new Events\ServerShutdown($this));
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerManagerStartCallback()
    {
        $this->swooleServer->on('ManagerStart', function () {
            if (php_uname('s') != 'Darwin') {
                swoole_set_process_name("{$this->app->name()} : Manager");
            }

            $this->events->dispatch(new Events\ManagerStarted($this));
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerManagerStopCallback()
    {
        $this->swooleServer->on('ManagerStop', function () {
            $this->events->dispatch(new Events\ManagerStopped($this));
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerWorkerStartCallback()
    {
        $this->swooleServer->on('WorkerStart', function ($server, $workerId) {
            if (php_uname('s') != 'Darwin') {
                swoole_set_process_name("{$this->app->name()} : Worker {$workerId}"
                    .($server->taskworker ? ' Task' : ''));
            }

            $this->events->dispatch(new Events\WorkerStarted($this, $workerId));
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerWorkerStopCallback()
    {
        $this->swooleServer->on('WorkerStop', function ($server, $workerId) {
            $this->events->dispatch(new Events\WorkerStopped($this, $workerId));
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerWorkerExitCallback()
    {
        $this->swooleServer->on('WorkerExit', function ($server, $workerId) {
            $this->events->dispatch(new Events\WorkerExiting($this, $workerId));
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerWorkerErrorCallback()
    {
        $this->swooleServer->on('WorkerError', function ($server, $workerId, $workerPid, $exitCode, $signal) {
            $this->reportWorkerError($workerId, $workerPid, $exitCode, $signal);

            $this->events->dispatch(new Events\WorkerError($this, $workerId, $workerPid, $exitCode, $signal));
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerTaskCallback()
    {
        $this->swooleServer->on('Task', function ($server, $taskId, $srcWorkerId, $data) {
            $this->events->dispatch(new Events\TaskTriggered($this, $taskId, $srcWorkerId, $data));
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerFinishCallback()
    {
        $this->swooleServer->on('Finish', function ($server, $taskId, $data) {
            $this->events->dispatch(new Events\TaskFinished($this, $taskId, $data));
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerPipeMessageCallback()
    {
        $this->swooleServer->on('PipeMessage', function ($server, $srcWorkerId, $data) {
            $this->events->dispatch(new Events\PipeMessageReceived($this, $srcWorkerId, $data));
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerBufferFullCallback()
    {
        $this->swooleServer->on('BufferFull', function ($server, $fd) {
            $this->serverSocket->dispatchEvent('BufferFull', $this, $this->serverSocket, $fd);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerBufferEmptyCallback()
    {
        $this->swooleServer->on('BufferEmpty', function ($server, $fd) {
            $this->serverSocket->dispatchEvent('BufferEmpty', $this, $this->serverSocket, $fd);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerConnectCallback()
    {
        $this->swooleServer->on('Connect', function ($server, $fd, $reactorId) {
            $this->serverSocket->dispatchEvent('Connect', $this, $this->serverSocket, $fd, $reactorId);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerCloseCallback()
    {
        $this->swooleServer->on('Close', function ($server, $fd, $reactorId) {
            $this->serverSocket->dispatchEvent('Close', $this, $this->serverSocket, $fd, $reactorId);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerReceiveCallback()
    {
        $this->swooleServer->on('Receive', function ($server, $fd, $reactorId, $data) {
            $this->serverSocket->dispatchEvent('Receive', $this, $this->serverSocket, $fd, $reactorId, $data);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerPacketCallback()
    {
        $this->swooleServer->on('Packet', function ($server, $data, $clientInfo) {
            $this->serverSocket->dispatchEvent('Packet', $this, $this->serverSocket, $data, $clientInfo);
        });
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

        $handler->renderForConsole($this->output, $e);
    }

    /**
     * 报告进程异常退出
     *
     * @param int $workerId
     * @param int $workerPid
     * @param int $exitCode
     * @param int $signal
     */
    protected function reportWorkerError($workerId, $workerPid, $exitCode, $signal)
    {
        $message = "Worker {$workerId} exit with code {$exitCode}, signal {$signal}, pid {$workerPid}.";

        $handler = $this->app->make(ExceptionHandler::class);

        $handler->report(new RuntimeException($message));

        $this->outputStyle->warn($message);
    }

    /**
     * 代理调用到 Swoole 服务
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->swooleServer->$method(...$parameters);
    }
}
