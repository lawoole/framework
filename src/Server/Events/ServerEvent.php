<?php
namespace Lawoole\Server\Events;

abstract class ServerEvent
{
    /**
     * 服务对象
     *
     * @var \Lawoole\Server\Server
     */
    public $server;

    /**
     * 创建服务事件
     *
     * @param \Lawoole\Server\Server $server
     */
    public function __construct($server)
    {
        $this->server = $server;
    }
}
