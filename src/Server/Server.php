<?php
namespace Lawoole\Server;

use EmptyIterator;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Events\Dispatcher;
use IteratorAggregate;
use Lawoole\Contracts\Server\Server as ServerContract;
use Lawoole\Contracts\Server\ServerSocket as ServerSocketContract;
use Lawoole\Server\ServerSockets\ServerSocket;
use RuntimeException;
use Swoole\Server as SwooleServer;
use Symfony\Component\Console\Output\OutputInterface;

class Server implements ServerContract, IteratorAggregate
{
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
     * 异常处理器
     *
     * @var \Illuminate\Contracts\Debug\ExceptionHandler
     */
    protected $exceptions;

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
        'Task', 'Finish', 'PipeMessage'
    ];

    /**
     * 创建服务对象
     *
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param int $processMode
     */
    public function __construct(ServerSocket $serverSocket, $processMode)
    {
        $this->serverSocket = $serverSocket;

        $this->swooleServer = $this->createSwooleServer($serverSocket, $processMode);

        $this->registerEventCallbacks($this->serverEvents);

        $this->events->dispatch(new Events\ServerCreated($this));
    }

    /**
     * 设置事件分发总线
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
    public function setEventDispatcher(Dispatcher $events)
    {
        $this->events = $events;
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
     * 设置异常处理器
     *
     * @param \Illuminate\Contracts\Debug\ExceptionHandler $exceptions
     */
    public function setExceptionHandler(ExceptionHandler $exceptions)
    {
        $this->exceptions = $exceptions;
    }

    /**
     * 获得异常处理器
     *
     * @return \Illuminate\Contracts\Debug\ExceptionHandler
     */
    public function getExceptionHandler()
    {
        return $this->exceptions;
    }

    /**
     * 设置控制台输出
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * 获得控制台输出
     *
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
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
        $this->serving = true;

        $this->events->dispatch(new Events\ServerLaunching($this));

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
     * 处理异常
     *
     * @param \Exception $e
     */
    protected function handleException(Exception $e)
    {
        if ($this->exceptions) {
            $this->exceptions->report($e);

            if ($this->output) {
                $this->exceptions->renderForConsole($this->output, $e);
            }
        }
    }
}
