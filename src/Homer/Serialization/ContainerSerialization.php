<?php
namespace Lawoole\Homer\Serialization;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerContract;

class ContainerSerialization
{
    /**
     * 创建容器传输包
     *
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public function __construct(ContainerContract $container)
    {
    }

    /**
     * 获得容器
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function getContainer()
    {
        return Container::getInstance();
    }
}
