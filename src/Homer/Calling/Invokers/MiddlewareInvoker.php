<?php
namespace Lawoole\Homer\Calling\Invokers;

use Lawoole\Homer\Calling\Invocation;
use Lawoole\Homer\Calling\Pipeline;

class MiddlewareInvoker extends Invoker
{
    /**
     * The container implementation.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The invoker to be called.
     *
     * @var \Lawoole\Homer\Calling\Invokers\Invoker
     */
    protected $invoker;

    /**
     * The middleware for the invoker.
     *
     * @var array
     */
    protected $middleware;

    /**
     * Create a concrete invoker instance.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param string $interface
     * @param \Lawoole\Homer\Calling\Invokers\Invoker $invoker
     * @param array $middleware
     * @param array $options
     */
    public function __construct($container, $interface, $invoker, $middleware, array $options = [])
    {
        parent::__construct($interface, $options);

        $this->container = $container;
        $this->invoker = $invoker;
        $this->middleware = $middleware;
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
        return (new Pipeline($this->container))
            ->send($invocation)
            ->through($this->middleware)
            ->then(function ($invocation) {
                return $this->invoker->invoke($invocation);
            });
    }
}
