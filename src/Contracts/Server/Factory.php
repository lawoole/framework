<?php
namespace Lawoole\Contracts\Server;

interface Factory
{
    /**
     * 获得服务对象
     *
     * @return \Lawoole\Contracts\Server\Server
     */
    public function server();
}
