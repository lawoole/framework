<?php
namespace Lawoole\Swoole;

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
     * @param int $type
     * @param array $options
     */
    public function __construct($unixSock, $type = SWOOLE_SOCK_UNIX_STREAM, array $options = [])
    {
        parent::__construct($unixSock, 0, $type, $options);
    }
}
