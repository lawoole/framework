<?php
namespace Lawoole\Homer\Components;

use Illuminate\Support\Arr;
use Lawoole\Homer\Calling\Dispatcher;
use Lawoole\Homer\Calling\Invokers\ConcreteInvoker;
use Lawoole\Homer\Context;
use LogicException;

class ServiceComponent extends Component
{
    /**
     * The invoking context instance.
     *
     * @var \Lawoole\Homer\Context
     */
    protected $context;

    /**
     * The invoking dispatcher.
     *
     * @var \Lawoole\Homer\Calling\Dispatcher
     */
    protected $dispatcher;

    /**
     * The interface invoker.
     *
     * @var \Lawoole\Homer\Calling\Invokers\Invoker
     */
    protected $invoker;

    /**
     * The invoking context instance.
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
     * Set the invoking dispatcher.
     *
     * @param \Lawoole\Homer\Calling\Dispatcher $dispatcher
     *
     * @return $this
     */
    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Export the interface.
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
     * Get invoker for the interface.
     *
     * @return \Lawoole\Homer\Calling\Invokers\Invoker
     */
    public function getInvoker()
    {
        if ($this->invoker !== null) {
            return $this->invoker;
        }

        return $this->invoker = $this->createInvoker();
    }

    /**
     * Create the invoker.
     *
     * @return \Lawoole\Homer\Calling\Invokers\Invoker
     */
    protected function createInvoker()
    {
        $config = $this->config;

        $interface = Arr::pull($config, 'interface');
        $concrete = Arr::pull($config, 'refer', $interface);

        $invoker = new ConcreteInvoker($this->context, $interface, $concrete, $config);

        return $this->withMiddleware($invoker, $interface, $config);
    }
}
