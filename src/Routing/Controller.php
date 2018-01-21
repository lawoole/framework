<?php
namespace Lawoole\Routing;

use BadMethodCallException;

abstract class Controller
{
    /**
     * 代理动态调用方法
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        throw new BadMethodCallException("Method [{$method}] does not exist.");
    }
}
