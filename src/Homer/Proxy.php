<?php
namespace Lawoole\Homer;

use BadMethodCallException;
use Illuminate\Support\Facades\Log;
use Lawoole\Homer\Invokers\Invoker;
use ReflectionClass;
use ReflectionException;

class Proxy
{
    /**
     * 调用器
     *
     * @var \Lawoole\Homer\Invokers\Invoker
     */
    protected $invoker;

    /**
     * 调用接口名
     *
     * @var string
     */
    protected $interface;

    /**
     * 调用接口反射
     *
     * @var \ReflectionClass
     */
    protected $reflection;

    /**
     * 接口方法
     *
     * @var array
     */
    protected $methods;

    /**
     * 创建调用代理
     *
     * @param \Lawoole\Homer\Invokers\Invoker $invoker
     *
     * @throws \ReflectionException
     */
    public function __construct(Invoker $invoker)
    {
        $this->invoker = $invoker;

        $this->parseInvoker($invoker);
    }

    /**
     * 分析调用器
     *
     * @param \Lawoole\Homer\Invokers\Invoker $invoker
     *
     * @throws \ReflectionException
     */
    protected function parseInvoker(Invoker $invoker)
    {
        $this->interface = $invoker->getInterface();

        try {
            $reflection = new ReflectionClass($this->interface);

            if (!$reflection->isInterface()) {
                throw new I("{$this->interface} must be an interface.");
            }

            $this->methods = $this->getMethods($reflection);

            $this->reflection = $reflection;
        } catch (ReflectionException $e) {
            Log::channel('homer')->warning("Reflect {$this->interface} failed.", [
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * 获得接口方法
     *
     * @param \ReflectionClass $reflection
     *
     * @return array
     */
    protected function getMethods(ReflectionClass $reflection)
    {
        $methods = [];

        foreach ($reflection->getMethods() as $method) {
            $methods[$method->getName()] = $reflection->getName().'->'.$method->getName();
        }

        return $methods;
    }

    /**
     * 获得调用器
     *
     * @return \Lawoole\Homer\Invokers\Invoker
     */
    public function getInvoker()
    {
        return $this->invoker;
    }

    /**
     * 获得调用接口类名
     *
     * @return string
     */
    public function getInterface()
    {
        return $this->interface;
    }

    /**
     * 执行调用
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     * @throws \Throwable
     */
    protected function call($method, array $arguments)
    {
        if (!isset($this->methods[$method])) {
            throw new BadMethodCallException("Method {$this->interface}::{$method} does not exist.");
        }

        $result = $this->invoker->invoke(
            $this->createInvocation($method, $arguments)
        );

        if (!$result instanceof Result) {
            $resultType = get_class($result) ?: 'unknown';

            throw new HomerException("Calling result must instance of Result, while the {$resultType} given.");
        }

        return $result->recover();
    }

    /**
     * 创建调用
     *
     * @param string $method
     * @param array $arguments
     *
     * @return \Lawoole\Homer\Invocation
     */
    protected function createInvocation($method, array $arguments)
    {
        return new Invocation($this->interface, $method, $arguments);
    }

    /**
     * 执行调用
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     * @throws \Throwable
     */
    public function __call($name, $arguments)
    {
        return $this->call($name, $arguments);
    }
}
