<?php
namespace Lawoole\Contracts\Stream;

interface InputStream extends Closeable
{
    /**
     * 判断是否已经读取到流的末尾
     *
     * @return bool
     */
    public function eof();

    /**
     * 获得当前流的指针位置
     *
     * @return int
     */
    public function tell();

    /**
     * 重置流指针到流的开始
     */
    public function rewind();

    /**
     * 从流中读取指定长度的字数据
     *
     * @param int $length
     *
     * @return string
     */
    public function read($length);
}
