<?php
namespace Lawoole\Contracts\Server;

use IteratorAggregate;
use Lawoole\Contracts\Support\EventDispatcher;

interface ServerSocket extends EventDispatcher, IteratorAggregate
{
    /**
     * Set the server socket options.
     *
     * @param array $options
     */
    public function setOptions(array $options);

    /**
     * Get the host.
     *
     * @return string
     */
    public function getHost();

    /**
     * Get the port number.
     *
     * @return int
     */
    public function getPort();

    /**
     * Get the socket type for this server socket.
     *
     * @return int
     */
    public function getSocketType();

    /**
     * Return whether the server socket has been bound to a server.
     *
     * @return bool
     */
    public function isBound();

    /**
     * Retrieve an iterator for all connections in this port.
     *
     * @return \Traversable
     */
    public function getIterator();
}