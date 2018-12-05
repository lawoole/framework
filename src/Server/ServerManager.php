<?php
namespace Lawoole\Server;

use Lawoole\Contracts\Server\Factory;

class ServerManager
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The server configurations.
     *
     * @var array
     */
    protected $config;

    /**
     * The server factory.
     *
     * @var \Lawoole\Server\ServerFactory
     */
    protected $factory;

    /**
     * The instance of the managed server.
     *
     * @var \Lawoole\Server\Server
     */
    protected $server;

    /**
     * Create a new server factory instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Lawoole\Contracts\Server\Factory $factory
     * @param array $config
     */
    public function __construct($app, Factory $factory, array $config)
    {
        $this->app = $app;
        $this->factory = $factory;
        $this->config = $config;
    }

    /**
     * Get the server instance.
     *
     * @return \Lawoole\Contracts\Server\Server
     */
    public function server()
    {
        if ($this->server != null) {
            return $this->server;
        }

        return $this->server = $this->createServer();
    }

    /**
     * Create the server.
     *
     * @return \Lawoole\Contracts\Server\Server
     */
    protected function createServer()
    {
        return $this->factory->make($this->config);
    }

    /**
     * Register an extension server resolver.
     *
     * @param string $driver
     * @param callable $resolver
     */
    public function extendServer($driver, callable $resolver)
    {
        $this->factory->extendServer($driver, $resolver);
    }

    /**
     * Register an extension server socket resolver.
     *
     * @param string $protocol
     * @param callable $resolver
     */
    public function extendServerSocket($protocol, callable $resolver)
    {
        $this->factory->extendServerSocket($protocol, $resolver);
    }

    /**
     * Pass methods onto the default Redis connection.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->server()->{$method}(...$parameters);
    }
}