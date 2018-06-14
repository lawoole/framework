<?php
namespace Lawoole\Server\Events;

class PipeMessageReceived extends ServerEvent
{
    /**
     * The worker id which message come from.
     *
     * @var int
     */
    public $srcWorkerId;

    /**
     * The message data.
     *
     * @var mixed
     */
    public $data;

    /**
     * Create a new event instance.
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
