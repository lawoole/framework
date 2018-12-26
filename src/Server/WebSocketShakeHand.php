<?php
namespace Lawoole\Server;

trait WebSocketShakeHand
{
    /**
     * Called when the server receive a WebSocket handshake request.
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function onHandShake($server, $serverSocket, $request, $response)
    {
        $secWebSocketAccept = base64_encode(
            sha1($request->header['sec-websocket-key'].'258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true)
        );

        $response->header('Upgrade', 'websocket');
        $response->header('Connection', 'Upgrade');
        $response->header('Sec-WebSocket-Accept', $secWebSocketAccept);
        $response->header('Sec-WebSocket-Version', '13');

        // WebSocket connection to 'ws://127.0.0.1:9502/'
        // failed: Error during WebSocket handshake:
        // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
        if (isset($request->header['sec-websocket-protocol'])) {
            $response->header('Sec-WebSocket-Protocol', $request->header['sec-websocket-protocol']);
        }

        $response->status(101);
        $response->end();

        if (method_exists($this, 'onOpen')) {
            $this->onOpen($server, $serverSocket, $request);
        }
    }
}
