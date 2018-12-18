<?php
namespace Lawoole\Server;

use BadMethodCallException;
use EmptyIterator;
use Illuminate\Contracts\Foundation\Application;
use Lawoole\Contracts\Server\Process as ProcessContract;
use Lawoole\Contracts\Server\Server as ServerContract;
use Lawoole\Contracts\Server\ServerSocket as ServerSocketContract;
use Lawoole\Contracts\Server\WorkerExitUnexpectedException;
use Lawoole\Support\DispatchEvents;
use LogicException;
use RuntimeException;
use Swoole\Server as SwooleServer;

class Server implements ServerContract
{
    use DispatchEvents;

    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * The Swoole server instance.
     *
     * @var \Swoole\Server
     */
    protected $swooleServer;

    /**
     * Whether the server is running.
     *
     * @var bool
     */
    protected $running = false;

    /**
     * The default server socket for the server.
     *
     * @var \Lawoole\Server\ServerSockets\ServerSocket
     */
    protected $serverSocket;

    /**
     * An array contains all bound server sockets.
     *
     * @var \Lawoole\Server\ServerSockets\ServerSocket[]
     */
    protected $serverSockets = [];

    /**
     * The server configurations.
     *
     * @var array
     */
    protected $config;

    /**
     * The server options.
     *
     * @var array
     */
    protected $options = [
        'dispatch_mode'    => 2,
        'enable_coroutine' => false,
        'reload_async'     => true,
    ];

    /**
     * An array of available server events.
     *
     * @var array
     */
    protected $serverEvents = [
        'Start', 'Shutdown', 'ManagerStart', 'ManagerStop',
        'WorkerStart', 'WorkerStop', 'WorkerExit', 'WorkerError',
        'Task', 'Finish', 'PipeMessage', 'BufferFull', 'BufferEmpty',
        'Connect', 'Close', 'Receive'
    ];

    /**
     * Create a server instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Lawoole\Contracts\Server\ServerSocket $serverSocket
     * @param array $config
     */
    public function __construct(Application $app, ServerSocketContract $serverSocket, array $config)
    {
        $this->app = $app;
        $this->events = $app['events'];

        $this->serverSocket = $serverSocket;

        $this->config = $config;

        $this->initialize($config);
    }

    /**
     * Initialize the server.
     *
     * @param array $config
     */
    protected function initialize(array $config)
    {
        $this->swooleServer = $this->createSwooleServer($config);

        $this->configureDefaultServerSocket();

        $this->setOptions($config['options'] ?? []);

        $this->registerEventCallbacks($this->serverEvents);

        $this->events->dispatch(new Events\ServerCreated($this));
    }

    /**
     * Create the Swoole server instance.
     *
     * @param array $config
     *
     * @return \Swoole\Server
     */
    protected function createSwooleServer(array $config)
    {
        return new SwooleServer(
            $this->serverSocket->getHost(),
            $this->serverSocket->getPort(),
            $this->parseServerMode($config),
            $this->serverSocket->getSocketType()
        );
    }

    /**
     * Get the server mode constant from the configuration.
     *
     * @param array $config
     *
     * @return int
     */
    protected function parseServerMode(array $config)
    {
        $mode = $config['mode'] ?? 'process';

        switch ($mode) {
            case 'base':
                return SWOOLE_BASE;
            case 'process':
                return SWOOLE_PROCESS;
            default:
                return $mode;
        }
    }

    /**
     * Get the Swoole server instance.
     *
     * @return \Swoole\Server
     */
    public function getSwooleServer()
    {
        return $this->swooleServer;
    }

    /**
     * Configure the default server socket.
     */
    protected function configureDefaultServerSocket()
    {
        $this->serverSocket->listen($this, head($this->swooleServer->ports));
    }

    /**
     * Set the server options.
     *
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        if ($this->isRunning()) {
            throw new LogicException('Options cannot be set while the server is running.');
        }

        $this->options = array_diff_key($this->options, $options) + $options;

        $this->swooleServer->set($this->options);
    }

    /**
     * Get the server options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Add a listening defined in the given server socket.
     *
     * @param \Lawoole\Contracts\Server\ServerSocket $serverSocket
     */
    public function listen(ServerSocketContract $serverSocket)
    {
        if ($this->isRunning()) {
            throw new LogicException('Cannot add listening while the server is serving.');
        }

        if ($serverSocket->isBound()) {
            throw new LogicException('The server socket can be bound to server only once.');
        }

        $address = "{$serverSocket->getHost()}:{$serverSocket->getPort()}";

        if (isset($this->serverSockets[$address])) {
            throw new LogicException('The same address has been listened before.');
        }

        $swoolePort = $this->swooleServer->listen(
            $serverSocket->getHost(),
            $serverSocket->getPort(),
            $serverSocket->getSocketType()
        );

        if ($swoolePort == false) {
            $errorCode = $this->swooleServer->getLastError();

            throw new RuntimeException(sprintf(
                'Listen %s%s fail, error code %d', $serverSocket->getHost(),
                $serverSocket->getPort() ? ':'.$serverSocket->getPort() : '', $errorCode
            ), $errorCode);
        }

        $this->serverSockets[$address] = $serverSocket;

        $serverSocket->listen($this, $swoolePort);
    }

    /**
     * Add a child process managed by the server.
     *
     * @param \Lawoole\Contracts\Server\Process $process
     */
    public function fork(ProcessContract $process)
    {
        $this->swooleServer->addProcess($process->getSwooleProcess());

        $process->bind($this);
    }

    /**
     * Return whether the server is running.
     *
     * @return bool
     */
    public function isRunning()
    {
        return $this->running;
    }

    /**
     * Start the server.
     *
     * @return bool
     */
    public function start()
    {
        $this->events->dispatch(new Events\ServerLaunching($this));

        $this->running = true;

        $this->dispatchEvent('Launching', $this);

        $result = $this->swooleServer->start();

        $this->running = false;

        return $result;
    }

    /**
     * Shutdown the server.
     *
     * @return bool
     */
    public function shutdown()
    {
        if (! $this->isRunning()) {
            return true;
        }

        return $this->swooleServer->shutdown();
    }

    /**
     * Reload all workers.
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
     * Retrieve an iterator for all connections connected to the server.
     *
     * @return \Traversable
     */
    public function getIterator()
    {
        if ($this->isRunning()) {
            return $this->swooleServer->connections;
        }

        return new EmptyIterator;
    }

    /**
     * Close the given connection.
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
     * Set the current process name.
     *
     * @param string $name
     */
    protected function setProcessName($name)
    {
        if (php_uname('s') != 'Darwin') {
            swoole_set_process_name($name);
        }
    }

    /**
     * Register all event callbacks.
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
     * Register the event callback.
     */
    protected function registerStartCallback()
    {
        $this->swooleServer->on('Start', function () {
            $this->setProcessName("{$this->app->name()} : Master");

            $this->events->dispatch(new Events\ServerStarted($this));

            $this->dispatchEvent('Start', $this);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerShutdownCallback()
    {
        $this->swooleServer->on('Shutdown', function () {
            $this->events->dispatch(new Events\ServerShutdown($this));

            $this->dispatchEvent('Shutdown', $this);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerManagerStartCallback()
    {
        $this->swooleServer->on('ManagerStart', function () {
            $this->setProcessName("{$this->app->name()} : Manager");

            $this->events->dispatch(new Events\ManagerStarted($this));

            $this->dispatchEvent('ManagerStart', $this);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerManagerStopCallback()
    {
        $this->swooleServer->on('ManagerStop', function () {
            $this->events->dispatch(new Events\ManagerStopped($this));

            $this->dispatchEvent('ManagerStop', $this);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerWorkerStartCallback()
    {
        $this->swooleServer->on('WorkerStart', function ($server, $workerId) {
            $this->setProcessName(sprintf(
                '%s : Worker %d%s', $this->app->name(), $workerId, $server->taskworker ? ' Task' : ''
            ));

            $this->events->dispatch(new Events\WorkerStarted($this, $workerId));

            $this->dispatchEvent('WorkerStart', $this, $workerId);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerWorkerStopCallback()
    {
        $this->swooleServer->on('WorkerStop', function ($server, $workerId) {
            $this->events->dispatch(new Events\WorkerStopped($this, $workerId));

            $this->dispatchEvent('WorkerStop', $this, $workerId);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerWorkerExitCallback()
    {
        $this->swooleServer->on('WorkerExit', function ($server, $workerId) {
            $this->events->dispatch(new Events\WorkerExiting($this, $workerId));

            $this->dispatchEvent('WorkerExit', $this, $workerId);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerWorkerErrorCallback()
    {
        $this->swooleServer->on('WorkerError', function ($server, $workerId, $workerPid, $exitCode, $signal) {
            $this->reportWorkerError($workerId, $workerPid, $exitCode, $signal);

            $this->events->dispatch(new Events\WorkerError($this, $workerId, $workerPid, $exitCode, $signal));

            $this->dispatchEvent('WorkerError', $this, $workerId, $workerPid, $exitCode, $signal);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerTaskCallback()
    {
        $this->swooleServer->on('Task', function ($server, $taskId, $srcWorkerId, $data) {
            $this->events->dispatch(new Events\TaskTriggered($this, $taskId, $srcWorkerId, $data));

            $this->dispatchEvent('Task', $this, $taskId, $srcWorkerId, $data);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerFinishCallback()
    {
        $this->swooleServer->on('Finish', function ($server, $taskId, $data) {
            $this->events->dispatch(new Events\TaskFinished($this, $taskId, $data));

            $this->dispatchEvent('Finish', $this, $taskId, $data);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerPipeMessageCallback()
    {
        $this->swooleServer->on('PipeMessage', function ($server, $srcWorkerId, $data) {
            $this->events->dispatch(new Events\PipeMessageReceived($this, $srcWorkerId, $data));

            $this->dispatchEvent('PipeMessage', $this, $srcWorkerId, $data);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerBufferFullCallback()
    {
        $this->swooleServer->on('BufferFull', function ($server, $fd) {
            $this->dispatchEvent('BufferFull', $this, $fd);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerBufferEmptyCallback()
    {
        $this->swooleServer->on('BufferEmpty', function ($server, $fd) {
            $this->dispatchEvent('BufferEmpty', $this, $fd);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerConnectCallback()
    {
        $this->swooleServer->on('Connect', function ($server, $fd, $reactorId) {
            $this->dispatchEvent('Connect', $this, $fd, $reactorId);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerCloseCallback()
    {
        $this->swooleServer->on('Close', function ($server, $fd, $reactorId) {
            $this->dispatchEvent('Close', $this, $fd, $reactorId);
        });
    }

    /**
     * Register the event callback.
     */
    protected function registerReceiveCallback()
    {
        $this->swooleServer->on('Receive', function () {});
    }

    /**
     * Report worker exit
     *
     * @param int $workerId
     * @param int $workerPid
     * @param int $exitCode
     * @param int $signal
     */
    protected function reportWorkerError($workerId, $workerPid, $exitCode, $signal)
    {
        $this->handleException(new WorkerExitUnexpectedException(sprintf(
            'The worker %d quit unexpected with exit code %d, signal %d, pid %d.',
            $workerId, $exitCode, $signal, $workerPid
        )));
    }

    /**
     * Dynamically pass methods to the Swoole server.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->swooleServer, $method)) {
            return $this->swooleServer->$method(...$parameters);
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}
