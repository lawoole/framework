<?php
namespace Lawoole\Homer\Components;

use Illuminate\Contracts\Container\Container;

abstract class Component
{
    /**
     * The container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

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
}
