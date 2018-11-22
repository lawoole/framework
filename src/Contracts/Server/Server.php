<?php
namespace Lawoole\Contracts\Server;

use IteratorAggregate;
use Lawoole\Contracts\Support\EventDispatcher;

interface Server extends EventDispatcher, IteratorAggregate
{
    /**
     * Set the server options.
     *
     * @param array $options
     */
    public function setOptions(array $options = []);

    /**
     * Add a listening defined in the given server socket.
     *
     * @param \Lawoole\Contracts\Server\ServerSocket $serverSocket
     */
    public function listen(ServerSocket $serverSocket);

    /**
     * Add a child process managed by the server.
     *
     * @param \Lawoole\Contracts\Server\Process $process
     */
    public function fork(Process $process);

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
     * Retrieve an iterator for all connections connected to the server.
     *
     * @return \Traversable
     */
    public function getIterator();
}
