<?php
namespace Lawoole\Homer\Components;

use Illuminate\Contracts\Container\Container;

abstract class Component
{
    /**
     * 容器
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * 配置
     *
     * @var array
     */
    protected $config;

    /**
     * 创建组件
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
