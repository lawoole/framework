<?php
namespace Lawoole\Server;

use EmptyIterator;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;
use IteratorAggregate;
use Lawoole\Contracts\Server\Server as ServerContract;
use Lawoole\Contracts\Server\WorkerExitUnexpectedException;
use Lawoole\Server\Concerns\DispatchEvents;
use Lawoole\Server\Process\Process;
use Lawoole\Server\ServerSockets\ServerSocket;
use LogicException;
use Swoole\Server as SwooleServer;
use Symfony\Component\Console\Output\OutputInterface;

class Server implements ServerContract, IteratorAggregate
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
     * The event handler.
     *
     * @var mixed
     */
    protected $handler;

    /**
     * The output for console.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * The styled output.
     *
     * @var \Illuminate\Console\OutputStyle
     */
    protected $outputStyle;

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
        'reload_async' => true
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
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param array $config
     */
    public function __construct(Application $app, ServerSocket $serverSocket, array $config)
    {
        $this->app = $app;
        $this->events = $app['events'];
        $this->output = $app['console.output'];
        $this->output = $app['console.output.style'];

        $this->serverSocket = $serverSocket;

        $this->config = $config;

        $this->initialize();
    }

    /**
     * Initialize the server.
     */
    protected function initialize()
    {
        $this->swooleServer = $this->createSwooleServer();

        $this->configureDefaultServerSocket();

        $this->setOptions($this->config['options'] ?? []);

        $this->registerEventCallbacks($this->serverEvents);

        $this->events->dispatch(new Events\ServerCreated($this));
    }

    /**
     * Create the Swoole server instance.
     *
     * @return \Swoole\Server
     */
    protected function createSwooleServer()
    {
        return new SwooleServer(
            $this->serverSocket->getHost(),
            $this->serverSocket->getPort(),
            $this->parseServerMode(),
            $this->serverSocket->getSocketType()
        );
    }

    /**
     * Get the server mode constant from the configuration.
     *
     * @return int
     */
    protected function parseServerMode()
    {
        $mode = $this->config['mode'] ?? 'process';

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
     * Set the event handler.
     *
     * @param mixed $handler
     *
     * @return $this
     */
    public function setEventHandler($handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * Get the event handler.
     *
     * @return mixed
     */
    public function getEventHandler()
    {
        return $this->handler;
    }

    /**
     * Set the output for console.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return $this
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Get the output for console.
     *
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Add a port listening.
     *
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     */
    public function listen(ServerSocket $serverSocket)
    {
        if ($this->isRunning()) {
            throw new LogicException('Cannot add listening while the server is serving.');
        }

        if ($serverSocket->isBound()) {
            throw new LogicException('The server socket can be bound to server only once.');
        }

        $address = $serverSocket->getHost().':'.$serverSocket->getPort();

        if (isset($this->serverSockets[$address])) {
            throw new LogicException('The same address has been listened before.');
        }

        $swoolePort = $this->swooleServer->listen(
            $serverSocket->getHost(),
            $serverSocket->getPort(),
            $serverSocket->getSocketType()
        );

        $this->serverSockets[$address] = $serverSocket;

        $serverSocket->listen($this, $swoolePort);
    }

    /**
     * Add a child process.
     *
     * @param \Lawoole\Server\Process\Process $process
     */
    public function fork(Process $process)
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
        if (!$this->isRunning()) {
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
     * Get an iterator for all connected connections.
     *
     * @return \Iterator
     */
    public function getConnectionIterator()
    {
        if ($this->isRunning()) {
            return $this->swooleServer->connections;
        }

        return new EmptyIterator;
    }

    /**
     * Get an iterator for all connected connections.
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        return $this->getConnectionIterator();
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
            $this->outputStyle->info("{$this->app->name()} server is running.");

            if (php_uname('s') != 'Darwin') {
                swoole_set_process_name("{$this->app->name()} : Master");
            }

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
            if (php_uname('s') != 'Darwin') {
                swoole_set_process_name("{$this->app->name()} : Manager");
            }

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
            if (php_uname('s') != 'Darwin') {
                swoole_set_process_name(sprintf('%s : Worker %d%s', $this->app->name(), $workerId
                    , $server->taskworker ? ' Task' : ''));
            }

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
        $message = sprintf('The worker %d quit unexpected with exit code %d, signal %d, pid %d.', $workerId, $exitCode,
            $signal, $workerPid);

        $handler = $this->app->make(ExceptionHandler::class);

        $handler->report(new WorkerExitUnexpectedException($message));

        $this->outputStyle->warn($message);
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
        return $this->swooleServer->$method(...$parameters);
    }
}
