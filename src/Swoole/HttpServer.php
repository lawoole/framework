<?php
namespace Lawoole\Swoole;

use Swoole\Http\Server as SwooleHttpServer;

class HttpServer extends Server
{
    /**
     * 创建 Swoole 服务
     *
     * @return \Swoole\Http\Server
     */
    protected function createSwooleServer()
    {
        return new SwooleHttpServer($this->getUnixSock(), 0, SWOOLE_PROCESS, SWOOLE_SOCK_UNIX_STREAM);
    }
}
