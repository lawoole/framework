<?php
namespace Lawoole\Stream;

use Lawoole\Contracts\Stream\Stream as StreamContract;
use RuntimeException;

class MemoryStream extends Stream implements StreamContract
{
    /**
     * The memory stream.
     *
     * @var resource
     */
    protected $stream;

    /**
     * The size of stream.
     *
     * @var int
     */
    protected $size = 0;

    /**
     * Create a memory stream.
     */
    public function __construct()
    {
        $this->stream = @fopen('php://memory', 'w+');

        if ($this->stream == false) {
            throw new RuntimeException('Cannot open the php://memory stream.');
        }
    }

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
        return !$this->stream || feof($this->stream);
    }

    /**
     * Returns the current position of the read/write pointer.
     *
     * @return int|bool
     */
    public function tell()
    {
        return $this->stream ? ftell($this->stream) : false;
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
        return $this->stream && fseek($this->stream, $offset, $whence) === 0;
    }

    /**
     * Rewind the read/write pointer to the head of the stream.
     *
     * @return bool
     */
    public function rewind()
    {
        return $this->stream && rewind($this->stream);
    }

    /**
     * Get the size of the stream if available.
     *
     * @return int|null
     */
    public function getSize()
    {
        if ($this->size !== null) {
            return $this->size;
        }

        $stats = fstat($this->stream);

        if (isset($stats['size'])) {
            $this->size = $stats['size'];

            return $this->size;
        }

        return null;
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
        if ($this->stream == null) {
            return false;
        }

        $this->size = null;

        return fwrite($this->stream, $string);
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
        return $this->stream ? fread($this->stream, $length) : false;
    }

    /**
     * Returns the remaining contents of the stream as a string.
     *
     * @return string
     */
    public function getContents()
    {
        return $this->stream ? (string) stream_get_contents($this->stream) : '';
    }

    /**
     * Attempts to seek to the beginning of the stream and reads all data into
     * a string until the end of the stream is reached.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->stream == null) {
            return '';
        }

        $this->seek(0);

        return (string) stream_get_contents($this->stream);
    }

    /**
     * Closes the stream and any underlying resources.
     */
    public function close()
    {
        parent::close();

        if ($this->stream) {
            fclose($this->stream);

            $this->stream = null;
            $this->size = 0;
        }
    }

    /**
     * Destroy the memory stream.
     */
    public function __destruct()
    {
        if (!$this->isClosed()) {
            $this->close();
        }
    }
}
