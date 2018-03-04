<?php
namespace Lawoole\Server\Events;

abstract class WorkerEvent extends ServerEvent
{
    /**
     * 工作进程 Id
     *
     * @var int
     */
    public $workerId;

    /**
     * 创建工作进程事件
     *
     * @param \Lawoole\Server\Server $server
     * @param int $workerId
     */
    public function __construct($server, $workerId)
    {
        parent::__construct($server);

        $this->workerId = $workerId;
    }
}
