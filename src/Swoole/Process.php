<?php
namespace Lawoole\Swoole;

use Swoole\Process as SwooleProcess;

class Process
{
    use HasEventHandlers;

    /**
     * 服务
     *
     * @var \Lawoole\Swoole\Server
     */
    protected $server;

    /**
     * Swoole 进程对象
     *
     * @var \Swoole\Process
     */
    protected $swooleProcess;

    /**
     * 守护进程标记
     *
     * @var bool
     */
    protected $daemon = false;

    /**
     * 创建进程
     */
    public function __construct()
    {
        $this->swooleProcess = $this->createProcess();
    }

    /**
     * 创建 Swoole 进程对象
     *
     * @return \Swoole\Process
     */
    protected function createProcess()
    {
        return new SwooleProcess(function () {
            $this->run();
        }, true, 1);
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
     * 设置守护进程
     *
     * @param bool $daemon
     */
    public function setDaemon($daemon)
    {
        $this->daemon = $daemon;
    }

    /**
     * 判断是否为守护进程
     *
     * @return bool
     */
    public function isDaemon()
    {
        return $this->daemon;
    }

    /**
     * 绑定到服务对象
     *
     * @param \Lawoole\Swoole\Server $server
     */
    public function bindToServer(Server $server)
    {
        $this->server = $server;
    }

    /**
     * 获得服务对象
     *
     * @return \Lawoole\Swoole\Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * 执行
     */
    protected function run()
    {
        if ($this->isDaemon()) {
            SwooleProcess::daemon(true, true);
        }

        $this->dispatchEvent('Start', $this);

        swoole_event_add($this->swooleProcess->pipe, [$this, 'onPipeRead']);
    }

    /**
     * 管道读取事件
     */
    protected function onPipeRead()
    {
        $data = $this->swooleProcess->read();

        $this->dispatchEvent('Receive', $this, $data);
    }

    /**
     * 执行外部程序
     *
     * @param string $executor
     * @param array $args
     *
     * @return bool
     */
    public function execute($executor, ...$args)
    {
        return $this->swooleProcess->exec($executor, $args);
    }

    /**
     * 设置进程间通讯管道超时
     *
     * @param float $timeout
     *
     * @return bool
     */
    public function setPipeTimeout($timeout)
    {
        return $this->swooleProcess->setTimeout($timeout);
    }

    /**
     * 启动进程
     */
    public function start()
    {
        $this->swooleProcess->start();
    }

    /**
     * 向进程管道中写入数据
     *
     * @param string $data
     *
     * @return int
     */
    public function write($data)
    {
        return $this->swooleProcess->write($data);
    }

    /**
     * 结束进程
     *
     * @param int $status 进程退出码
     */
    public function quit($status = 0)
    {
        $this->swooleProcess->exit($status);
    }
}
