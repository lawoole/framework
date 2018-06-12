<?php
namespace Lawoole\Stream;

use Lawoole\Contracts\Stream\Stream as StreamContract;

abstract class Stream implements StreamContract
{
    /**
     * Whether the stream is closed.
     *
     * @var bool
     */
    protected $closed = false;

    /**
     * Return whether the stream is closed.
     *
     * @return bool
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * Closes the stream and any underlying resources.
     */
    public function close()
    {
        $this->closed = true;
    }
}
