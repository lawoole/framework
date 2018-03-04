<?php
namespace Lawoole\Server\Events;

class TaskFinished extends ServerEvent
{
    /**
     * 任务 Id
     *
     * @var int
     */
    public $taskId;

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
     * @param mixed $data
     */
    public function __construct($server, $taskId, $data)
    {
        parent::__construct($server);

        $this->taskId = $taskId;
        $this->data = $data;
    }
}
