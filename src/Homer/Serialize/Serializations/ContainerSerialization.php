<?php
namespace Lawoole\Homer\Serialize\Serializations;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerContract;

class ContainerSerialization extends Serialization
{
    /**
     * Create a container serialization instance.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public function __construct(ContainerContract $container)
    {
    }

    /**
     * Get the container instance from the serialization.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function recover()
    {
        return Container::getInstance();
    }
}
