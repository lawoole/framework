<?php
namespace Lawoole\Homer\Invokers;

use Closure;
use Lawoole\Homer\Context;
use Lawoole\Homer\HomerException;
use Lawoole\Homer\Invocation;
use Lawoole\Homer\Result;

class ConcreteInvoker extends Invoker
{
    /**
     * 调用上下文
     *
     * @var \Lawoole\Homer\Context
     */
    protected $context;

    /**
     * 调用实体
     *
     * @var string|object|\Closure
     */
    protected $concrete;

    /**
     * 调用实体对象实例
     *
     * @var object|\Closure
     */
    protected $instance;

    /**
     * 创建实体调用器
     *
     * @param \Lawoole\Homer\Context $context
     * @param string $interface
     * @param string|object|\Closure $concrete
     * @param array $options
     */
    public function __construct(Context $context, $interface, $concrete, array $options = [])
    {
        parent::__construct($interface, $options);

        $this->context = $context;
        $this->concrete = $concrete;
    }

    /**
     * 判断是否允许动态调用
     *
     * @return bool
     */
    public function isDynamic()
    {
        return $this->options['dynamic'] ?? false;
    }

    /**
     * 执行调用并得到调用结果
     *
     * @param \Lawoole\Homer\Invocation $invocation
     *
     * @return \Lawoole\Homer\Result
     */
    protected function doInvoke(Invocation $invocation)
    {
        $this->context->setInvocation($invocation);

        try {
            $concrete = $this->getConcrete();

            if (is_object($concrete)) {
                $result = call_user_func_array([$concrete, $invocation->getMethod()], $invocation->getArguments());
            } elseif ($concrete instanceof Closure) {
                $result = call_user_func($concrete, $invocation);
            } else {
                throw new HomerException('Invoking failed while the concrete cannot be invoked.');
            }

            return $this->createInvokeResult($result);
        } finally {
            $this->context->forgetInvocation();
        }
    }

    /**
     * 获得实际调用对象
     *
     * @return object|\Closure
     */
    public function getConcrete()
    {
        if ($this->instance) {
            return $this->instance;
        }

        if (is_object($this->concrete) || $this->concrete instanceof Closure) {
            $instance = $this->concrete;
        } else {
            $instance = $this->context->getContainer()->make($this->concrete);
        }

        // 如果开启动态调用，则允许使用未实现接口的对象，甚至是闭包接收调用
        if (!$this->isDynamic()) {
            $this->checkConcreteInstance($instance);
        }

        return $this->instance = $instance;
    }

    /**
     * 检查调用实体对象实例
     *
     * @param object|\Closure $instance
     */
    protected function checkConcreteInstance($instance)
    {
        $interface = $this->getInterface();

        if (!is_object($instance)) {
            throw new HomerException("The concrete instance for {$interface} must be an object.");
        }

        if (!$instance instanceof $interface) {
            throw new HomerException("The concrete must an instance of {$interface}.");
        }
    }

    /**
     * 创建调用结果
     *
     * @param mixed $value
     *
     * @return \Lawoole\Homer\Result
     */
    protected function createInvokeResult($value)
    {
        return new Result($value);
    }
}
