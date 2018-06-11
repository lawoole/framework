<?php
namespace Lawoole\Contracts\Server;

interface ServerSocket
{
    /**
     * Get the server.
     *
     * @return \Lawoole\Contracts\Server\Server
     */
    public function getServer();

    /**
     * Get the Swoole port instance.
     *
     * @return \Swoole\Server\Port
     */
    public function getSwoolePort();

    /**
     * Return whether the ServerSocket has been bound to a server.
     *
     * @return bool
     */
    public function isBound();

    /**
     * Get an iterator for all connected connections in the ServerSocket.
     *
     * @return \Iterator
     */
    public function getConnectionIterator();
}
