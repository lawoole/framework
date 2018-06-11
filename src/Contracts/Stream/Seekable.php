<?php
namespace Lawoole\Contracts\Stream;

interface Seekable
{
    /**
     * Returns whether the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable();

    /**
     * Returns whether the pointer is at the end of the stream.
     *
     * @return bool
     */
    public function eof();

    /**
     * Returns the current position of the read/write pointer.
     *
     * @return int|bool
     */
    public function tell();

    /**
     * Seek to a position in the stream.
     *
     * @param int $offset Stream offset.
     * @param int $whence Specifies how the cursor position will be calculated.
     *
     * @return bool
     */
    public function seek($offset, $whence = SEEK_SET);

    /**
     * Rewind the read/write pointer to the head of the stream.
     *
     * @return bool
     */
    public function rewind();
}