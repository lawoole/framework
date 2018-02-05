<?php
namespace Lawoole\Swoole\Handlers;

interface ClientHandlerInterface
{
    /**
     * 在客户端开始连接时调用
     *
     * @param \Lawoole\Swoole\Server $server
     */
    public function onStart($server);

    /**
     * 在客户端连接成功时调用
     *
     * @param \Lawoole\Swoole\Client $client
     */
    public function onConnect($client);

    /**
     * 在客户端连接失败时调用
     *
     * @param \Lawoole\Swoole\Client $client
     */
    public function onError($client);

    /**
     * 在客户端收到数据时调用
     *
     * @param \Lawoole\Swoole\Client $client
     * @param string $data
     */
    public function onReceive($client, $data);

    /**
     * 在客户端连接关闭时调用
     *
     * @param \Lawoole\Swoole\Client $client
     */
    public function onClose($client);

    /**
     * 在完成 SSL 连接时调用
     *
     * @param \Lawoole\Swoole\Client $client
     */
    public function onSslReady($client);
}
