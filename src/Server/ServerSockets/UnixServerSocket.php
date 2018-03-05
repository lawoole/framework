<?php
namespace Lawoole\Server\ServerSockets;

use Lawoole\Contracts\Foundation\Application;

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
     * @param \Lawoole\Contracts\Foundation\Application $app
     * @param array $config
     */
    public function __construct(Application $app, array $config = [])
    {
        $unixSock = $config['unix_sock'];

        $config['host'] = $unixSock;
        $config['port'] = 0;

        parent::__construct($app, $config);
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
