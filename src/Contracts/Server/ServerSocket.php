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
     * 获得 Swoole 端口对象
     *
     * @return \Swoole\Server\Port
     */
    public function getSwoolePort();

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
