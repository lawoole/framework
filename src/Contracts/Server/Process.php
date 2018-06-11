<?php
namespace Lawoole\Contracts\Server;

interface Process
{
    /**
     * Get the Swoole process instance.
     *
     * @return \Swoole\Process
     */
    public function getSwooleProcess();

    /**
     * Quit the process.
     *
     * @param int $status
     */
    public function quit($status = 0);
}
