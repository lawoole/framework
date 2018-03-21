<?php
namespace Lawoole\Contracts\Stream;

interface OutputStream extends Closeable
{
    /**
     * 向流中写入数据
     *
     * @param string $string
     *
     * @return int
     */
    public function write($string);

    /**
     * 立即刷写流中的数据
     */
    public function flush();
}
