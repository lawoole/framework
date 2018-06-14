<?php
namespace Lawoole\Contracts\Server;

interface Server
{
    /**
     * Get the Swoole server instance.
     *
     * @return \Swoole\Server
     */
    public function getSwooleServer();

    /**
     * Return whether the server is running.
     *
     * @return bool
     */
    public function isRunning();

    /**
     * Start the server.
     *
     * @return bool
     */
    public function start();

    /**
     * Shutdown the server.
     *
     * @return bool
     */
    public function shutdown();

    /**
     * Reload all workers.
     *
     * @param bool $onlyTask
     *
     * @return bool
     */
    public function reload($onlyTask = false);

    /**
     * Get an iterator for all connected connections.
     *
     * @return \Iterator
     */
    public function getConnectionIterator();
}
