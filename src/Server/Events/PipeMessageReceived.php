<?php
namespace Lawoole\Server\Events;

class PipeMessageReceived extends ServerEvent
{
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
     * @param int $srcWorkerId
     * @param mixed $data
     */
    public function __construct($server, $srcWorkerId, $data)
    {
        parent::__construct($server);

        $this->srcWorkerId = $srcWorkerId;
        $this->data = $data;
    }
}
