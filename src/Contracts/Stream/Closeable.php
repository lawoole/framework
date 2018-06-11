<?php
namespace Lawoole\Contracts\Stream;

interface Closeable
{
    /**
     * Returns whether the stream is closed.
     *
     * @return bool
     */
    public function isClosed();

    /**
     * Closes the stream and any underlying resources.
     */
    public function close();
}
