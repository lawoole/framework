<?php
namespace Lawoole\Swoole\Handlers;

interface WebSocketClientHandlerInterface
{
    /**
     * 在收到 WebSocket 消息帧时调用
     *
     * @param \Lawoole\Swoole\WebSocketClient $client
     * @param \Swoole\WebSocket\Frame $frame
     */
    public function onMessage($client, $frame);
}
