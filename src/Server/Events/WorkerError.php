<?php
namespace Lawoole\Server\Events;

class WorkerError extends WorkerEvent
{
    /**
     * 工作进程 Pid
     *
     * @var int
     */
    public $workerPid;

    /**
     * 工作进程退出码
     *
     * @var int
     */
    public $exitCode;

    /**
     * 触发信号
     *
     * @var int
     */
    public $signal;

    /**
     * 创建工作进程错误退出事件
     *
     * @param \Lawoole\Server\Server $server
     * @param int $workerId
     * @param int $workerPid
     * @param int $exitCode
     * @param int $signal
     */
    public function __construct($server, $workerId, $workerPid, $exitCode, $signal)
    {
        parent::__construct($server, $workerId);

        $this->workerPid = $workerPid;
        $this->exitCode = $exitCode;
        $this->signal = $signal;
    }
}
