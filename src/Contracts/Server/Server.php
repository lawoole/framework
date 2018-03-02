<?php
namespace Lawoole\Contracts\Server;

interface Server
{
    /**
     * 添加监听定义
     *
     * @param \Lawoole\Contracts\Server\ServerSocket $serverSocket
     */
    public function listen(ServerSocket $serverSocket);

    /**
     * 判断服务是否正在运行
     *
     * @return bool
     */
    public function isServing();

    /**
     * 启动服务
     */
    public function serve();

    /**
     * 停止服务
     */
    public function shutdown();

    /**
     * 获得所有当前连接的迭代器
     *
     * @return \Iterator
     */
    public function getConnectionIterator();
}
