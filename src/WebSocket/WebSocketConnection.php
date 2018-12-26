<?php
namespace Lawoole\WebSocket;

use Lawoole\Contracts\WebSocket\Connection;

class WebSocketConnection implements Connection
{
    /**
     * The server instance.
     *
     * @var \Lawoole\Server\WebSocketServer
     */
    protected $server;

    /**
     * The identify of the connection.
     *
     * @var int
     */
    protected $id;

    /**
     * Create a web socket connection instance.
     *
     * @param \Lawoole\Server\Server $server
     * @param int $id
     */
    public function __construct($server, $id)
    {
        $this->server = $server;
        $this->id = $id;
    }

    /**
     * Get the identify of the connection.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return whether the connection is active.
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->server->isEstablished($this->id);
    }

    /**
     * Push data to the client.
     *
     * @param string $data
     * @param bool $finish
     * @param bool $binary
     *
     * @return bool
     */
    public function push($data, $finish = true, $binary = false)
    {
        return $this->server->push(
            $this->id, $data, $binary ? WEBSOCKET_OPCODE_BINARY : WEBSOCKET_OPCODE_TEXT, $finish
        );
    }

    /**
     * Push binary data to the client.
     *
     * @param string $data
     * @param bool $finish
     *
     * @return bool
     */
    public function pushBinary($data, $finish = true)
    {
        return $this->push($data, $finish, true);
    }

    /**
     * Disconnect to the client.
     *
     * @param int $code
     * @param string $reason
     *
     * @return bool
     */
    public function disconnect($code = 1000, $reason = '')
    {
        return $this->server->disconnect($this->id, $code, $reason);
    }
}
