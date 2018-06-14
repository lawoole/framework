<?php
namespace Lawoole\Server\Process;

use Illuminate\Contracts\Foundation\Application;
use Lawoole\Contracts\Server\Process as ProcessContract;
use Lawoole\Server\Concerns\DispatchEvents;
use Lawoole\Server\Server;
use LogicException;
use Swoole\Process as SwooleProcess;
use Symfony\Component\Console\Output\OutputInterface;

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
     * Create a new server socket instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param array $config
     */
    public function __construct(Application $app, OutputInterface $output, array $config)
    {
        $this->app = $app;
        $this->output = $output;
        $this->config = $config;
    }

    /**
     * 创建 Swoole 进程对象
     *
     * @return \Swoole\Process
     */
    protected function createSwooleProcess()
    {
        return new SwooleProcess(function ($process) {
            $process->name(sprintf('%s : Process %s', $this->app->name(), $this->getName() ?: $process->pid));

            $this->dispatchEvent('Start', $this->server, $this);

            swoole_event_add($this->swooleProcess->pipe, function () {
                if (false !== ($data = $this->swooleProcess->read())) {
                    $this->dispatchEvent('Message', $this->server, $this, $data);
                }
            });

            call_user_func($this->runnableRetriever(), $this->server, $this);
        }, false, true);
    }

    /**
     * Get the process name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->config['name'] ?? null;
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

        return function () {
            $this->dispatchEvent('Run', $this->server, $this);
        };
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
     * Get the Swoole process instance.
     *
     * @return \Swoole\Process
     */
    public function getSwooleProcess()
    {
        return $this->swooleProcess;
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
     * Send message to the process.
     *
     * @param string $data
     */
    public function write($data)
    {
        $this->swooleProcess->write($data);
    }

    /**
     * Exit the process with given status.
     *
     * @param int $status
     */
    public function quit($status = 0)
    {
        $this->swooleProcess->exit($status);
    }
}
