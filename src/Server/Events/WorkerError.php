<?php
namespace Lawoole\Server\Events;

class WorkerError extends WorkerEvent
{
    /**
     * The process id for the worker.
     *
     * @var int
     */
    public $workerPid;

    /**
     * The process exit status code.
     *
     * @var int
     */
    public $exitCode;

    /**
     * The signal to trigger the error.
     *
     * @var int
     */
    public $signal;

    /**
     * Create a new event instance.
     *
     * @param \Lawoole\Server\Server $server
     * @param int $workerId
     * @param int $workerPid
     * @param int $exitCode
     * @param int $signal
     */
    public function __construct($server, $workerId, $workerPid, $exitCode, $signal)
    {
        parent::__construct($server, $workerId);

        $this->workerPid = $workerPid;
        $this->exitCode = $exitCode;
        $this->signal = $signal;
    }
}
