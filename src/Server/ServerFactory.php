<?php
namespace Lawoole\Server;

use Lawoole\Contracts\Server\Factory;

class ServerFactory implements Factory
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
        // TODO: Implement make() method.
    }
}
