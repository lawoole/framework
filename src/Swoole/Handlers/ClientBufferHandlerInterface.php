<?php
namespace Lawoole\Swoole\Handlers;

interface ClientBufferHandlerInterface
{
    /**
     * 当缓存区达到高位线时调用
     *
     * @param \Lawoole\Swoole\Client $client
     */
    public function onBufferFull($client);

    /**
     * 当缓存区降至低位线时调用

     * @param \Lawoole\Swoole\Client $client
     */
    public function onBufferEmpty($client);
}
