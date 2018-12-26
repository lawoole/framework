<?php
namespace Lawoole\WebSocket;

use Lawoole\Http\HttpServerSocketHandler;

class WebSocketServerSocketHandler extends HttpServerSocketHandler
{
    /**
     * An array of all active connection.
     *
     * @var array
     */
    protected $connections = [];

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
        $swapHttpRequest = $this->app['request'];

        $httpRequest = $this->createHandShakeRequest($request);

        $respondent = $this->createRespondent($response);

        $httpRequest->attributes->add([
            'request'    => $request,
            'response'   => $response,
            'respondent' => $respondent
        ]);

        $httpResponse = $this->handleRequest($httpRequest, $respondent);

        if ($httpResponse instanceof HandShakeResponse && $httpResponse->isAccepted()) {
            $this->acceptConnection($server, $request, $httpResponse->getMessageHandler());
        }

        $this->sendResponse($respondent, $httpResponse);

        $this->app->instance('request', $swapHttpRequest);
    }

    /**
     * Called when the server receive a WebSocket message frame.
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param \Swoole\WebSocket\Frame $frame
     */
    public function onMessage($server, $serverSocket, $frame)
    {
        $id = $frame->fd;

        if (isset($this->connections[$id]) && $this->connections[$id]['handler']) {
            call_user_func(
                $this->connections[$id]['handler'],
                $this->connections[$id]['connection'],
                $frame->opcode == WEBSOCKET_OPCODE_BINARY,
                $frame->data
            );
        }
    }

    /**
     * Called when a connection has been closed.
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param int $fd
     */
    public function onClose($server, $serverSocket, $fd)
    {
        unset($this->connections[$fd]);
    }

    /**
     * Create a handshake request from the Swoole request.
     *
     * @param \Swoole\Http\Request $request
     *
     * @return \Illuminate\Http\Request
     */
    protected function createHandShakeRequest($request)
    {
        return HandShakeRequest::createFrom(
            $this->createHttpRequest($request)
        );
    }

    /**
     * Accept the web socket connection.
     *
     * @param \Lawoole\Server\Server $server
     * @param \Swoole\Http\Request $request
     * @param callable $handler
     */
    protected function acceptConnection($server, $request, $handler)
    {
        $id = $request->fd;

        $connection = $this->createWebSocketConnection($server, $id);

        $this->connections[$id] = compact($connection, $handler);
    }

    /**
     * Create a web socket connection instance.
     *
     * @param \Lawoole\Server\Server $server
     * @param int $id
     *
     * @return \Lawoole\WebSocket\WebSocketConnection
     */
    protected function createWebSocketConnection($server, $id)
    {
        return new WebSocketConnection($server, $id);
    }
}
