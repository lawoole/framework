<?php
namespace Lawoole\Contracts\Server;

use Lawoole\Contracts\Support\EventDispatcher;

interface Process extends EventDispatcher
{
    /**
     * Get the process's name.
     *
     * @return string
     */
    public function getName();

    /**
     * Return whether the process is running.
     *
     * @return bool
     */
    public function isRunning();

    /**
     * Return whether the process has been bound to a server.
     *
     * @return bool
     */
    public function isBound();

    /**
     * Stop the process executing.
     *
     * @param int $status
     */
    public function stop($status = 0);
}
