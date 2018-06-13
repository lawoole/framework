<?php
namespace Lawoole\Server;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Lawoole\Contracts\Server\Factory as FactoryContract;

class ServerFactory implements FactoryContract
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Create a new server factory instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Establish a server based on the configuration.
     *
     * @param array $config
     *
     * @return \Lawoole\Contracts\Server\Server
     */
    public function make(array $config)
    {
        $driver = $this->getDriver($config);

        if (method_exists($this, $method = 'create'.Str::studly($driver).'Driver')) {
            return $this->$method($config);
        }

        throw new InvalidArgumentException("Driver [{$driver}] is not supported.");
    }

    /**
     * Get the driver type of the server.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getDriver(array $config)
    {
        return $config['driver'] ?? 'tcp';
    }

    protected function createTcpDriver(array $config)
    {

    }

    protected function createHttpDriver(array $config)
    {

    }

    protected function createWebsocketDriver(array $config)
    {

    }


    protected function getDefaultServerSocket(array $config)
    {

    }
}
