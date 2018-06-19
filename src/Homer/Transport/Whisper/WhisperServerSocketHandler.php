<?php
namespace Lawoole\Homer\Transport\Whisper;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Lawoole\Homer\Calling\Dispatcher;
use Lawoole\Homer\Serialize\Factory as SerializerFactory;
use Lawoole\Homer\Transport\SerializeServerSocketMessages;
use Throwable;

class WhisperServerSocketHandler
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
     * Create a Whisper socket handler.
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
        return 'swoole';
    }

    /**
     * 从连接中取得数据时调用
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     */
    public function onReceive($server, $serverSocket, $fd, $reactorId, $data)
    {
        try {
            $serializer = $this->getSerializer($serverSocket);

            $message = $serializer->deserialize(substr($data, 4));

            $result = $this->dispatcher->handleMessage($message);

            $body = $serializer->serialize($result);

            $this->respond($server, $fd, 200, $body);
        } catch (Throwable $e) {
            Log::channel('homer')->warning('Handle invoking failed, cause: '.$e->getMessage(), [
                'exception' => $e
            ]);

            $this->respond($server, $fd, 500, $e->getMessage());

            $server->closeConnection($fd);
        }
    }

    /**
     * Send the response.
     *
     * @param \Lawoole\Server\Server $server
     * @param int $fd
     * @param int $status
     * @param string $body
     */
    protected function respond($server, $fd, $status, $body)
    {
        $swooleServer = $server->getSwooleServer();

        $swooleServer->send($fd, pack('n', $status));
        $swooleServer->send($fd, pack('N', strlen($body)));
        $swooleServer->send($fd, $body);
    }
}
