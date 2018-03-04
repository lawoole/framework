<?php
namespace Lawoole\Server\Events;

class TaskTriggered extends ServerEvent
{
    /**
     * 任务 Id
     *
     * @var int
     */
    public $taskId;

    /**
     * 来源工作进程 Id
     *
     * @var int
     */
    public $srcWorkerId;

    /**
     * 数据
     *
     * @var mixed
     */
    public $data;

    /**
     * 创建任务推送事件
     *
     * @param \Lawoole\Server\Server $server
     * @param int $taskId
     * @param int $srcWorkerId
     * @param mixed $data
     */
    public function __construct($server, $taskId, $srcWorkerId, $data)
    {
        parent::__construct($server);

        $this->taskId = $taskId;
        $this->srcWorkerId = $srcWorkerId;
        $this->data = $data;
    }
}
