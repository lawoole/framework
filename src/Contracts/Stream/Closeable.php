<?php
namespace Lawoole\Contracts\Stream;

interface Closeable
{
    /**
     * 关闭流并释放与之相关的系统资源
     */
    public function close();
}
