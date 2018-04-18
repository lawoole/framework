<?php
namespace Lawoole\Homer\Components;

use Illuminate\Support\Arr;
use Lawoole\Homer\Context;
use Lawoole\Homer\Dispatcher;
use Lawoole\Homer\Invokers\ConcreteInvoker;
use LogicException;

class ServiceComponent extends Component
{
    /**
     * 调用上下文
     *
     * @var \Lawoole\Homer\Context
     */
    protected $context;

    /**
     * 调用调度器
     *
     * @var \Lawoole\Homer\Dispatcher
     */
    protected $dispatcher;

    /**
     * 服务调用器
     *
     * @var \Lawoole\Homer\Invokers\Invoker
     */
    protected $invoker;

    /**
     * 设置调用上下文
     *
     * @param \Lawoole\Homer\Context $context
     *
     * @return $this
     */
    public function setContext(Context $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * 设置调用调度器
     *
     * @param \Lawoole\Homer\Dispatcher $dispatcher
     *
     * @return $this
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * 暴露服务
     */
    public function export()
    {
        if ($this->config == null) {
            throw new LogicException('Context must be set before export the service.');
        }

        if ($this->dispatcher == null) {
            throw new LogicException('Dispatcher must be set before export the service.');
        }

        $this->dispatcher->exportInvoker(
            $this->getInvoker()
        );
    }

    /**
     * 获得服务调用器
     *
     * @return \Lawoole\Homer\Invokers\Invoker
     */
    public function getInvoker()
    {
        if ($this->invoker !== null) {
            return $this->invoker;
        }

        return $this->invoker = $this->createInvoker();
    }

    /**
     * 创建服务调用器
     *
     * @return \Lawoole\Homer\Invokers\Invoker
     */
    protected function createInvoker()
    {
        $config = $this->config;

        $interface = Arr::pull($config, 'interface');
        $concrete = Arr::pull($config, 'refer', $interface);

        return new ConcreteInvoker($this->context, $interface, $concrete, $config);
    }
}