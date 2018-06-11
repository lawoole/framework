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
     * Add a port listening.
     *
     * @param \Lawoole\Contracts\Server\ServerSocket $serverSocket
     */
    public function listen(ServerSocket $serverSocket);

    /**
     * Add a child process.
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
     */
    public function start();

    /**
     * Shutdown the server.
     */
    public function shutdown();

    /**
     * Get an iterator for all connected connections.
     *
     * @return \Iterator
     */
    public function getConnectionIterator();
}
