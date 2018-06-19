<?php
namespace Lawoole\Homer\Transport\Http;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Lawoole\Homer\Calling\Dispatcher;
use Lawoole\Homer\Serialize\Factory as SerializerFactory;
use Lawoole\Homer\Transport\SerializeServerSocketMessages;
use Throwable;

class HttpServerSocketHandler
{
    use SerializeServerSocketMessages;

    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The invoker dispatcher.
     *
     * @var \Lawoole\Homer\Calling\Dispatcher
     */
    protected $dispatcher;

    /**
     * Create a Http socket handler.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Lawoole\Homer\Calling\Dispatcher $dispatcher
     * @param \Lawoole\Homer\Serialize\Factory $serializerFactory
     */
    public function __construct(Application $app, Dispatcher $dispatcher, SerializerFactory $serializerFactory)
    {
        $this->app = $app;
        $this->dispatcher = $dispatcher;
        $this->serializerFactory = $serializerFactory;
    }

    /**
     * Get default serialize type.
     *
     * @return string
     */
    protected function getDefaultSerializer()
    {
        return 'php';
    }

    /**
     * Called when the server receive a Http request.
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function onRequest($server, $serverSocket, $request, $response)
    {
        try {
            $serializer = $this->getSerializer($serverSocket);

            $message = $serializer->deserialize($request->rawcontent());

            $result = $this->dispatcher->handleMessage($message);

            $body = $serializer->serialize($result);

            $this->respond($response, 200, $body);
        } catch (Throwable $e) {
            Log::channel('homer')->warning('Handle invoking failed, cause: '.$e->getMessage(), [
                'exception' => $e
            ]);

            $this->respond($response, 500, $e->getMessage());
        }
    }

    /**
     * Send the response.
     *
     * @param \Swoole\Http\Response $response
     * @param int $status
     * @param string $body
     */
    protected function respond($response, $status, $body)
    {
        $response->status($status);

        $response->end($body);
    }
}
