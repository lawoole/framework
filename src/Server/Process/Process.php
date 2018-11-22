<?php
namespace Lawoole\Server\Process;

use Illuminate\Contracts\Foundation\Application;
use Lawoole\Contracts\Server\Process as ProcessContract;
use Lawoole\Server\Concerns\DispatchEvents;
use Lawoole\Server\Server;
use LogicException;
use Swoole\Process as SwooleProcess;

class Process implements ProcessContract
{
    use DispatchEvents;

    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The server instance.
     *
     * @var \Lawoole\Contracts\Server\Server
     */
    protected $server;

    /**
     * The Swoole process instance.
     *
     * @var \Swoole\Process
     */
    protected $swooleProcess;

    /**
     * The server socket configurations.
     *
     * @var array
     */
    protected $config;

    /**
     * Whether the process is running.
     *
     * @var bool
     */
    protected $running = false;

    /**
     * Create a new server socket instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param array $config
     */
    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;

        $this->swooleProcess = $this->createSwooleProcess();
    }

    /**
     * Create the Swoole process instance.
     *
     * @return \Swoole\Process
     */
    protected function createSwooleProcess()
    {
        $process = new SwooleProcess(function ($process) {
            $this->running = true;

            $process->name(sprintf('%s : Process %s', $this->app->name(), $this->getName() ?: $process->pid));

            $this->dispatchEvent('Start', $this->server, $this);

            swoole_event_add($this->swooleProcess->pipe, function () {
                if (false !== ($data = $this->swooleProcess->read())) {
                    $this->dispatchEvent('Message', $this->server, $this, $data);
                }
            });

            call_user_func($this->runnableRetriever(), $this->server, $this);
        }, false, true);

        $process->setBlocking($this->isBlocking());

        return $process;
    }

    /**
     * Get the Swoole process instance.
     *
     * @return \Swoole\Process
     */
    public function getSwooleProcess()
    {
        return $this->swooleProcess;
    }

    /**
     * Get the process's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->config['name'] ?? null;
    }

    /**
     * Return whether the process is a daemon process.
     *
     * @return bool
     */
    public function isDaemon()
    {
        return $this->config['daemon'] ?? false;
    }

    /**
     * Return whether the process's pipes are blocking.
     *
     * @return bool
     */
    public function isBlocking()
    {
        return $this->config['blocking'] ?? true;
    }

    /**
     * Send message to the process.
     *
     * @param string $data
     */
    public function write($data)
    {
        $this->swooleProcess->write($data);
    }

    /**
     * Return whether the process is running.
     *
     * @return bool
     */
    public function isRunning()
    {
        return $this->running;
    }

    /**
     * Get the runnable for the process.
     *
     * @return string|callable
     */
    public function getRunnable()
    {
        return $this->config['runnable'] ?? null;
    }

    /**
     * Get the runnable for the process.
     *
     * @return callable
     */
    protected function runnableRetriever()
    {
        $runnable = $this->getRunnable();

        if ($runnable && is_callable($runnable)) {
            return $runnable;
        }

        return function ($server, $process) {
            $this->dispatchEvent('Run', $server, $process);
        };
    }

    /**
     * Bind the process to the server.
     *
     * @param \Lawoole\Server\Server $server
     */
    public function bind(Server $server)
    {
        if ($this->isBound()) {
            throw new LogicException('Process can be bound to server only once.');
        }

        $this->server = $server;

        $this->dispatchEvent('Bind', $server, $this);
    }

    /**
     * Return whether the process has been bound to a server.
     *
     * @return bool
     */
    public function isBound()
    {
        return $this->server != null;
    }

    /**
     * Stop the process executing.
     *
     * @param int $status
     */
    public function stop($status = 0)
    {
        $this->swooleProcess->exit($status);
    }
}
