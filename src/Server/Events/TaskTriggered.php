<?php
namespace Lawoole\Server\Events;

class TaskTriggered extends ServerEvent
{
    /**
     * The task id.
     *
     * @var int
     */
    public $taskId;

    /**
     * The worker id which task come from.
     *
     * @var int
     */
    public $srcWorkerId;

    /**
     * The input data.
     *
     * @var mixed
     */
    public $data;

    /**
     * Create a new event instance.
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
