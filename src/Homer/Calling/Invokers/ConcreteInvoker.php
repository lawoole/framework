<?php
namespace Lawoole\Homer\Calling\Invokers;

use Closure;
use InvalidArgumentException;
use Lawoole\Homer\Calling\Invocation;
use Lawoole\Homer\Calling\Result;
use Lawoole\Homer\Context;

class ConcreteInvoker extends Invoker
{
    /**
     * The invoking context instance.
     *
     * @var \Lawoole\Homer\Context
     */
    protected $context;

    /**
     * The concrete to be called.
     *
     * @var string|object|\Closure
     */
    protected $concrete;

    /**
     * The concrete instance.
     *
     * @var object|\Closure
     */
    protected $instance;

    /**
     * Create a concrete invoker instance.
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
     * Return whether the concrete can be called dynamically.
     *
     * @return bool
     */
    public function isDynamic()
    {
        return $this->options['dynamic'] ?? false;
    }

    /**
     * Do invoking and get the result.
     *
     * @param \Lawoole\Homer\Calling\Invocation $invocation
     *
     * @return \Lawoole\Homer\Calling\Result
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
                throw new InvalidArgumentException('Invoking failed while the concrete cannot be invoked.');
            }

            return $this->createInvokeResult($result);
        } finally {
            $this->context->forgetInvocation();
        }
    }

    /**
     * Get the concrete instance.
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

        if (!$this->isDynamic()) {
            $this->checkConcreteInstance($instance);
        }

        return $this->instance = $instance;
    }

    /**
     * Check the concrete instance before it be called.
     *
     * @param object|\Closure $instance
     */
    protected function checkConcreteInstance($instance)
    {
        $interface = $this->getInterface();

        if (!is_object($instance)) {
            throw new InvalidArgumentException("The concrete instance for {$interface} must be an object.");
        }

        if (!$instance instanceof $interface) {
            throw new InvalidArgumentException("The concrete must an instance of {$interface}.");
        }
    }

    /**
     * Create a invoking result.
     *
     * @param mixed $value
     *
     * @return \Lawoole\Homer\Calling\Result
     */
    protected function createInvokeResult($value)
    {
        return new Result($value);
    }
}
