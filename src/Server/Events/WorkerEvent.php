<?php
namespace Lawoole\Server\Events;

abstract class WorkerEvent extends ServerEvent
{
    /**
     * The worker id.
     *
     * @var int
     */
    public $workerId;

    /**
     * Create a new event instance.
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
