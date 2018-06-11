<?php
namespace Lawoole\Contracts\Stream;

interface Stream extends Closeable, Seekable
{
    /**
     * Get the size of the stream if available.
     *
     * @return int|null
     */
    public function getSize();

    /**
     * Return whether the stream is writable.
     *
     * @return bool
     */
    public function isWritable();

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     *
     * @return int|bool
     */
    public function write($string);

    /**
     * Return whether the stream is readable.
     *
     * @return bool
     */
    public function isReadable();

    /**
     * Read data from the stream.
     *
     * @param int $length Length for the data to be read.
     *
     * @return string|bool
     */
    public function read($length);

    /**
     * Return the remaining contents of the stream as a string.
     *
     * @return string
     */
    public function getContents();

    /**
     * Attempts to seek to the beginning of the stream and reads all data into
     * a string until the end of the stream is reached.
     *
     * @return string
     */
    public function __toString();
}
