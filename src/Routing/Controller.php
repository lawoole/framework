<?php
namespace Lawoole\Routing;

use BadMethodCallException;
use Lawoole\Application;

abstract class Controller
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Application
     */
    protected $app;

    /**
     * 创建控制器
     *
     * @param \Lawoole\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 代理动态调用方法
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        throw new BadMethodCallException("Method [{$method}] does not exist");
    }
}
