<?php
namespace Lawoole\Task;

use Lawoole\Support\TransferableInterface;

abstract class Task extends Message implements TransferableInterface
{
    /**
     * 任务 Id
     *
     * @var int
     */
    protected $taskId;

    /**
     * 设置任务 Id
     *
     * @param int $taskId
     */
    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;
    }

    /**
     * 获得任务 Id
     *
     * @return int
     */
    public function getTaskId()
    {
        return $this->taskId;
    }
}
