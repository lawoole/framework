<?php
namespace Lawoole\WebSocket;

use Exception;
use Lawoole\Http\HttpServerSocketHandler;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

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
            $this->acceptConnection($server, $request, $httpRequest, $httpResponse->getHandler());
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

        if (isset($this->connections[$id])) {
            $this->fireMessageEvent(
                $this->connections[$id]['handler'],
                $this->connections[$id]['connection'],
                $frame
            );
        }
    }

    /**
     * Called when a connection has been closed.
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param int $id
     */
    public function onClose($server, $serverSocket, $id)
    {
        if (isset($this->connections[$id])) {
            $this->fireCloseEvent(
                $this->connections[$id]['handler'],
                $this->connections[$id]['connection']
            );

            unset($this->connections[$id]);
        }
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
     * @param \Illuminate\Http\Request $httpRequest
     * @param \Lawoole\Contracts\WebSocket\WebSocketHandler $handler
     */
    protected function acceptConnection($server, $request, $httpRequest, $handler)
    {
        $id = $request->fd;

        $connection = $this->createWebSocketConnection($server, $id);

        $this->fireOpenEvent($handler, $connection, $httpRequest);

        $this->connections[$id] = compact('connection', 'handler');
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

    /**
     * Fire the connection opened event.
     *
     * @param \Lawoole\Contracts\WebSocket\WebSocketHandler $handler
     * @param \Lawoole\Contracts\WebSocket\Connection $connection
     * @param \Illuminate\Http\Request $request
     */
    protected function fireOpenEvent($handler, $connection, $request)
    {
        $this->fireWebSocketHandler($handler, 'onOpen', $connection, $request);
    }

    /**
     * Fire the message received event.
     *
     * @param \Lawoole\Contracts\WebSocket\WebSocketHandler $handler
     * @param \Lawoole\Contracts\WebSocket\Connection $connection
     * @param \Swoole\WebSocket\Frame $frame
     */
    protected function fireMessageEvent($handler, $connection, $frame)
    {
        $isBinary = $frame->opcode == WEBSOCKET_OPCODE_BINARY;

        $this->fireWebSocketHandler($handler, 'onMessage', $connection, $isBinary, $frame->data);
    }

    /**
     * Fire the connection closed event.
     *
     * @param \Lawoole\Contracts\WebSocket\WebSocketHandler $handler
     * @param \Lawoole\Contracts\WebSocket\Connection $connection
     */
    protected function fireCloseEvent($handler, $connection)
    {
        $this->fireWebSocketHandler($handler, 'onClose', $connection);
    }

    /**
     * Send the event to the handler.
     *
     * @param \Lawoole\Contracts\WebSocket\WebSocketHandler $handler
     * @param string $method
     * @param array $arguments
     */
    protected function fireWebSocketHandler($handler, $method, ...$arguments)
    {
        if ($handler == null) {
            return;
        }

        try {
            call_user_func([$handler, $method], ...$arguments);
        } catch (Exception $e) {
            $this->reportException($e);
        } catch (Throwable $e) {
            $this->reportException(new FatalThrowableError($e));
        }
    }
}
