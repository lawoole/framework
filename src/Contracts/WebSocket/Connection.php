<?php
namespace Lawoole\Contracts\WebSocket;

interface Connection
{
    /**
     * Get the identify of the connection.
     *
     * @return int
     */
    public function getId();

    /**
     * Return whether the connection is active.
     *
     * @return bool
     */
    public function isConnected();

    /**
     * Push data to the client.
     *
     * @param string $data
     * @param bool $finish
     * @param bool $isBinary
     *
     * @return bool
     */
    public function push($data, $finish = true, $isBinary = false);

    /**
     * Push binary data to the client.
     *
     * @param string $data
     * @param bool $finish
     *
     * @return bool
     */
    public function pushBinary($data, $finish = true);

    /**
     * Disconnect to the client.
     *
     * @param int $code
     * @param string $reason
     *
     * @return bool
     */
    public function disconnect($code = 1000, $reason = '');
}
