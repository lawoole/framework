<?php
namespace Lawoole\Stream;

use Lawoole\Contracts\Stream\Stream as StreamContract;

class NullStream extends Stream implements StreamContract
{
    /**
     * Returns whether the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        return true;
    }

    /**
     * Returns whether the pointer is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        return true;
    }

    /**
     * Returns the current position of the read/write pointer.
     *
     * @return int|bool
     */
    public function tell()
    {
        return 0;
    }

    /**
     * Seek to a position in the stream.
     *
     * @param int $offset Stream offset.
     * @param int $whence Specifies how the cursor position will be calculated.
     *
     * @return bool
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        return false;
    }

    /**
     * Rewind the read/write pointer to the head of the stream.
     *
     * @return bool
     */
    public function rewind()
    {
        return false;
    }

    /**
     * Get the size of the stream if available.
     *
     * @return int|null
     */
    public function getSize()
    {
        return 0;
    }

    /**
     * Returns whether the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        return true;
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     *
     * @return int|bool
     */
    public function write($string)
    {
        return false;
    }

    /**
     * Returns whether the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Length for the data to be read.
     *
     * @return string|bool
     */
    public function read($length)
    {
        return false;
    }

    /**
     * Returns the remaining contents of the stream as a string.
     *
     * @return string
     */
    public function getContents()
    {
        return '';
    }

    /**
     * Attempts to seek to the beginning of the stream and reads all data into
     * a string until the end of the stream is reached.
     *
     * @return string
     */
    public function __toString()
    {
        return '';
    }
}