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
    protected $resource;

    /**
     * 数据总长度
     *
     * @var int
     */
    protected $length;

    /**
     * Create a memory stream.
     */
    public function __construct()
    {
        $this->resource = @fopen('php://memory', 'w+');

        if ($this->resource == false) {
            throw new RuntimeException('Cannot open the php://memory stream.');
        }
    }

    /**
     * 检查流是否已经关闭
     */
    protected function check()
    {
        if ($this->resource == null) {
            throw new RuntimeException('Cannot use the stream cause it had been closed.');
        }
    }

    /**
     * 重置流指针到流的开始
     */
    public function rewind()
    {
        $this->check();

        return rewind($this->resource);
    }

    /**
     * 从流中读取指定长度的字数据
     *
     * @param int $length
     *
     * @return string
     */
    public function read($length)
    {
        $this->check();

        return fread($this->resource, $length);
    }

    /**
     * 向流中写入数据
     *
     * @param string $string
     *
     * @return int
     */
    public function write($string)
    {
        $this->check();

        $position = ftell($this->resource);

        $length = fwrite($this->resource, $string);

        $this->length = max($this->length, $position + $length);

        return $length;
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
        return $this->resource && ftell($this->resource) >= $this->length;
    }

    /**
     * Returns the current position of the read/write pointer.
     *
     * @return int|bool
     */
    public function tell()
    {
        // TODO: Implement tell() method.
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
        // TODO: Implement seek() method.
    }

    /**
     * Rewind the read/write pointer to the head of the stream.
     *
     * @return bool
     */
    public function rewind()
    {
        // TODO: Implement rewind() method.
    }

    /**
     * Get the size of the stream if available.
     *
     * @return int|null
     */
    public function getSize()
    {
        // TODO: Implement getSize() method.
    }

    /**
     * Returns whether the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        // TODO: Implement isWritable() method.
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
        // TODO: Implement write() method.
    }

    /**
     * Returns whether the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        // TODO: Implement isReadable() method.
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
        // TODO: Implement read() method.
    }

    /**
     * Returns the remaining contents of the stream as a string.
     *
     * @return string
     */
    public function getContents()
    {
        // TODO: Implement getContents() method.
    }

    /**
     * Attempts to seek to the beginning of the stream and reads all data into
     * a string until the end of the stream is reached.
     *
     * @return string
     */
    public function __toString()
    {
        // TODO: Implement __toString() method.
    }

    /**
     * Closes the stream and any underlying resources.
     */
    public function close()
    {
        parent::close();

        if ($this->resource) {
            fclose($this->resource);

            $this->resource = null;
            $this->length = 0;
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
