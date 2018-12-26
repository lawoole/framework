<?php
namespace Lawoole\WebSocket;

use Illuminate\Http\Response;
use InvalidArgumentException;

class HandShakeResponse extends Response
{
    /**
     * The handler for the WebSocket message.
     *
     * @var callable
     */
    protected $messageHandler;

    /**
     * Return whether the response is to accept the WebSocket connection or not.
     *
     * @return bool
     */
    public function isAccepted()
    {
        return $this->getStatusCode() == self::HTTP_SWITCHING_PROTOCOLS;
    }

    /**
     * Get the message handler.
     *
     * @return callable
     */
    public function getMessageHandler()
    {
        return $this->messageHandler;
    }

    /**
     * Set the message handler.
     *
     * @param callable $handler
     */
    public function setMessageHandler(callable $handler)
    {
        $this->messageHandler = $handler;
    }

    /**
     * Accept the handshake request and open the connection.
     *
     * @param \Lawoole\WebSocket\HandShakeRequest $request
     * @param callable $handler
     *
     * @return static
     */
    public static function accept($request, $handler)
    {
        static::checkRequest($request);

        $webSocketAccept = base64_encode(
            sha1($request->getWebSocketKey().'258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true)
        );

        $response = static::create('', self::HTTP_SWITCHING_PROTOCOLS);

        $response->header('Upgrade', 'websocket');
        $response->header('Connection', 'Upgrade');
        $response->header('Sec-WebSocket-Accept', $webSocketAccept);
        $response->header('Sec-WebSocket-Version', '13');

        // WebSocket connection to 'ws://127.0.0.1:9502/'
        // failed: Error during WebSocket handshake:
        // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
        if ($webSocketProtocol = $request->getWebSocketProtocol()) {
            $response->header('Sec-WebSocket-Protocol', $webSocketProtocol);
        }

        $response->setMessageHandler($handler);

        return $response;
    }

    /**
     * Reject the handshake request and close the connection.
     *
     * @param \Lawoole\WebSocket\HandShakeRequest $request
     * @param int $status
     *
     * @return static
     */
    public static function reject($request, $status = self::HTTP_BAD_REQUEST)
    {
        static::checkRequest($request);

        return static::create('', $status);
    }

    /**
     * Verify the handshake request.
     *
     * @param \Illuminate\Http\Request $request
     */
    protected static function checkRequest($request)
    {
        if (! $request instanceof HandShakeRequest) {
            throw new InvalidArgumentException('The request must be an instance of HandShakeRequest.');
        }
    }
}
