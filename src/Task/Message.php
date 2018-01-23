<?php
namespace Lawoole\Task;

use Lawoole\Support\TransferableInterface;

abstract class Message implements TransferableInterface
{
    /**
     * 来源工作进程 Id
     *
     * @var int
     */
    protected $srcWorkerId;

    /**
     * 设置来源工作进程 Id
     *
     * @param int $srcWorkerId
     */
    public function setSrcWorkerId($srcWorkerId)
    {
        $this->srcWorkerId = $srcWorkerId;
    }

    /**
     * 获得来源工作进程 Id
     *
     * @return int
     */
    public function getSrcWorkerId()
    {
        return $this->srcWorkerId;
    }
}
