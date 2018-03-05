<?php
namespace Lawoole\Server\ServerSockets;

class UnixServerSocket extends ServerSocket
{
    /**
     * 服务 Socket 选项
     *
     * @var array
     */
    protected $options = [];

    /**
     * 创建服务 Socket 对象
     *
     * @param string $unixSock
     * @param array $options
     */
    public function __construct($unixSock, array $options = [])
    {
        parent::__construct($unixSock, 0, $options);
    }

    /**
     * 获得默认的 Socket 类型
     *
     * @return int
     */
    protected function getDefaultSocketType()
    {
        return SWOOLE_SOCK_UNIX_STREAM;
    }
}
