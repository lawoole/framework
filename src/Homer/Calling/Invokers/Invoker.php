<?php
namespace Lawoole\Homer\Calling\Invokers;

use Lawoole\Homer\Calling\Invocation;
use Lawoole\Homer\Calling\Result;
use Throwable;

abstract class Invoker
{
    /**
     * The interface name.
     *
     * @var string
     */
    protected $interface;

    /**
     * Calling options.
     *
     * @var array
     */
    protected $options;

    /**
     * Create a invoker instance.
     *
     * @param string $interface
     * @param array $options
     */
    public function __construct($interface, array $options = [])
    {
        $this->interface = $interface;
        $this->options = $options;
    }

    /**
     * Get interface name.
     *
     * @return string
     */
    public function getInterface()
    {
        return $this->interface;
    }

    /**
     * Return whether in debug mode.
     *
     * @return bool
     */
    protected function isDebug()
    {
        return $this->options['debug'] ?? false;
    }

    /**
     * Do invoking.
     *
     * @param \Lawoole\Homer\Calling\Invocation $invocation
     *
     * @return \Lawoole\Homer\Calling\Result
     */
    public function invoke(Invocation $invocation)
    {
        try {
            return $this->doInvoke($invocation);
        } catch (Throwable $e) {
            return $this->createExceptionResult($e);
        }
    }

    /**
     * Do invoking and get the result.
     *
     * @param \Lawoole\Homer\Calling\Invocation $invocation
     *
     * @return \Lawoole\Homer\Calling\Result
     */
    abstract protected function doInvoke(Invocation $invocation);

    /**
     * Create a result with exception.
     *
     * @param \Throwable $e
     *
     * @return \Lawoole\Homer\Calling\Result
     */
    protected function createExceptionResult(Throwable $e)
    {
        return new Result(null, $e);
    }
}
