<?php
namespace Lawoole\Contracts\Server;

interface ServerSocket
{
    /**
     * 获得服务对象
     *
     * @return \Lawoole\Contracts\Server\Server
     */
    public function getServer();

    /**
     * 判断是否已经绑定到服务
     *
     * @return bool
     */
    public function isBound();

    /**
     * 获得所有当前连接的迭代器
     *
     * @return \Iterator
     */
    public function getConnectionIterator();
}
