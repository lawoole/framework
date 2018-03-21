<?php
namespace Lawoole\Stream;

use Lawoole\Contracts\Stream\InputStream;
use Lawoole\Contracts\Stream\OutputStream;
use RuntimeException;

class MemoryStream implements InputStream, OutputStream
{
    /**
     * 流资源
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
     * 创建内存流
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
     * 判断是否已经读取到流的末尾
     *
     * @return bool
     */
    public function eof()
    {
        $this->check();

        return ftell($this->resource) >= $this->length;
    }

    /**
     * 获得当前流的指针位置
     *
     * @return int
     */
    public function tell()
    {
        $this->check();

        return ftell($this->resource);
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
     * 立即刷写流中的数据
     */
    public function flush()
    {
    }

    /**
     * 关闭流并释放与之相关的系统资源
     */
    public function close()
    {
        if ($this->resource) {
            fclose($this->resource);

            $this->resource = null;
            $this->length = 0;
        }
    }

    /**
     * 释放流
     */
    public function __destruct()
    {
        $this->close();
    }
}
