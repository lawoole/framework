<?php
namespace Lawoole\Homer\Components;

use Illuminate\Contracts\Container\Container;
use Lawoole\Homer\Calling\Invokers\MiddlewareInvoker;

abstract class Component
{
    /**
     * The container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The invoking middleware.
     *
     * @var array
     */
    protected $middleware;

    /**
     * The component config.
     *
     * @var array
     */
    protected $config;

    /**
     * Create a component instance.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param array $config
     */
    public function __construct(Container $container, array $config = [])
    {
        $this->container = $container;
        $this->config = $config;
    }

    /**
     * The invoking middleware.
     *
     * @param array $middleware
     *
     * @return $this
     */
    public function setMiddleware(array $middleware)
    {
        $this->middleware = $middleware;

        return $this;
    }

    /**
     * Add middleware to the invoker.
     *
     * @param \Lawoole\Homer\Calling\Invokers\Invoker $invoker
     * @param string $interface
     * @param array $config
     *
     * @return \Lawoole\Homer\Calling\Invokers\Invoker
     */
    protected function withMiddleware($invoker, $interface, $config)
    {
        if (empty($this->middleware)) {
            return $invoker;
        }

        return new MiddlewareInvoker($this->container, $interface, $invoker, $this->middleware, $config);
    }
}
