<?php
namespace Lawoole\Server\Process;

use Lawoole\Contracts\Foundation\Application;
use Lawoole\Contracts\Server\Process as ProcessContract;
use Lawoole\Server\Concerns\DispatchEvents;
use Lawoole\Server\Server;
use LogicException;
use Swoole\Process as SwooleProcess;

class Process implements ProcessContract
{
    use DispatchEvents;

    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * 服务对象
     *
     * @var \Lawoole\Contracts\Server\Server
     */
    protected $server;

    /**
     * 进程运行过程
     *
     * @var callable
     */
    protected $runnable;

    /**
     * 进程名
     *
     * @var string
     */
    protected $name;

    /**
     * Swoole 进程对象
     *
     * @var \Swoole\Process
     */
    protected $swooleProcess;

    /**
     * 事件处理器
     *
     * @var \Lawoole\Server\Process\ProcessHandler
     */
    protected $handler;

    /**
     * 控制台输出
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * 创建服务进程对象
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     * @param callable $runnable
     * @param string $name
     */
    public function __construct(Application $app, $runnable = null, $name = null)
    {
        $this->app = $app;
        $this->output = $app['console.output'];

        $this->runnable = $runnable;
        $this->name = $name;
    }

    /**
     * 创建 Swoole 进程对象
     *
     * @return \Swoole\Process
     */
    protected function createSwooleProcess()
    {
        return new SwooleProcess(function () {
            swoole_set_process_name(sprintf('%s : Process %s', $this->app->name(),
                $this->name ?: $this->swooleProcess->pid));

            $this->dispatchEvent('Start', $this);

            if (is_callable($this->runnable)) {
                call_user_func($this->runnable, $this->server, $this);
            }

            swoole_event_add($this->swooleProcess->pipe, function () {
                $data = $this->swooleProcess->read();

                if ($data !== false) {
                    $this->dispatchEvent('Message', $this->server, $this, $data);
                }
            });
        }, false, true);
    }

    /**
     * 获得进程名
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 设置进程名
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * 获得处理器
     *
     * @return \Lawoole\Server\Process\ProcessHandler
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * 设置处理器
     *
     * @param \Lawoole\Server\Process\ProcessHandler
     */
    public function setHandler(ProcessHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * 获得 Swoole 进程对象
     *
     * @return \Swoole\Process
     */
    public function getSwooleProcess()
    {
        return $this->swooleProcess;
    }

    /**
     * 绑定服务对象
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
     * 判断是否已经绑定到服务
     *
     * @return bool
     */
    public function isBound()
    {
        return $this->server != null;
    }

    /**
     * 向进程传递数据
     *
     * @param string $data
     */
    public function write($data)
    {
        $this->swooleProcess->write($data);
    }

    /**
     * 退出进程
     *
     * @param int $status
     */
    public function quit($status = 0)
    {
        $this->swooleProcess->exit($status);
    }
}
