<?php
namespace Lawoole\Contracts\WebSocket;

interface WebSocketHandler
{
    /**
     * Called when web socket connection is open.
     *
     * @param \Lawoole\Contracts\WebSocket\Connection $connection
     * @param \Illuminate\Http\Request $request
     */
    public function onOpen($connection, $request);

    /**
     * Called when received a web socket message.
     *
     * @param \Lawoole\Contracts\WebSocket\Connection $connection
     * @param bool $isBinary
     * @param string $data
     */
    public function onMessage($connection, $isBinary, $data);

    /**
     * Called when web socket connection is closed.
     *
     * @param \Lawoole\Contracts\WebSocket\Connection $connection
     */
    public function onClose($connection);
}
