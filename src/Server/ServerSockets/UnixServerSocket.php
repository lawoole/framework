<?php
namespace Lawoole\Server\ServerSockets;

use Illuminate\Support\Str;

class UnixServerSocket extends ServerSocket
{
    /**
     * The server socket options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Get the host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->config['unix_sock'] ?? $this->getDefaultHost();
    }

    /**
     * Get default host for listening.
     *
     * @return string
     */
    protected function getDefaultHost()
    {
        return $this->app->storagePath().'/framework/server-'.Str::random(6).'.sock';
    }

    /**
     * Get default port number for listening.
     *
     * @return int
     */
    protected function getDefaultPort()
    {
        return 0;
    }

    /**
     * Get default socket type for this server socket.
     *
     * @return int
     */
    protected function getDefaultSocketType()
    {
        return SWOOLE_SOCK_UNIX_STREAM;
    }
}
