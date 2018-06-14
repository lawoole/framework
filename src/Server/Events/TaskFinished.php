<?php
namespace Lawoole\Server\Events;

class TaskFinished extends ServerEvent
{
    /**
     * The task id.
     *
     * @var int
     */
    public $taskId;

    /**
     * The task result.
     *
     * @var mixed
     */
    public $data;

    /**
     * Create a new event instance.
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
