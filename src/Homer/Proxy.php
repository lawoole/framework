<?php
namespace Lawoole\Homer;

use BadMethodCallException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Lawoole\Homer\Invokers\Invoker;
use ReflectionClass;
use Throwable;

class Proxy
{
    /**
     * The invoker uses for calling.
     *
     * @var \Lawoole\Homer\Invokers\Invoker
     */
    protected $invoker;

    /**
     * The bound interface name.
     *
     * @var string
     */
    protected $interface;

    /**
     * The reflection for the interface.
     *
     * @var \ReflectionClass
     */
    protected $reflection;

    /**
     * The instance methods.
     *
     * @var array
     */
    protected $methods;

    /**
     * Create a invoking proxy instance.
     *
     * @param \Lawoole\Homer\Invokers\Invoker $invoker
     */
    public function __construct(Invoker $invoker)
    {
        $this->invoker = $invoker;

        $this->parseInvoker($invoker);
    }

    /**
     * Parse the invoker.
     *
     * @param \Lawoole\Homer\Invokers\Invoker $invoker
     */
    protected function parseInvoker(Invoker $invoker)
    {
        $this->interface = $invoker->getInterface();

        try {
            $reflection = new ReflectionClass($this->interface);

            if (!$reflection->isInterface()) {
                throw new InvalidArgumentException("{$this->interface} must be an interface.");
            }

            $this->methods = $this->getMethods($reflection);

            $this->reflection = $reflection;
        } catch (Throwable $e) {
            Log::channel('homer')->warning("Reflect {$this->interface} failed.", [
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    /**
     * Get instance methods.
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
     * Get the invoker.
     *
     * @return \Lawoole\Homer\Invokers\Invoker
     */
    public function getInvoker()
    {
        return $this->invoker;
    }

    /**
     * Get the interface name.
     *
     * @return string
     */
    public function getInterface()
    {
        return $this->interface;
    }

    /**
     * Do the calling.
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

            Log::channel('homer')->warning("The result is not a Result instance.", [
                'type'   => $resultType,
                'result' => var_export($result, true),
            ]);

            throw new HomerException("Calling result must instance of Result, while the {$resultType} given.");
        }

        return $result->recover();
    }

    /**
     * Create a invocation.
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
     * Delegate the calling to the invoker.
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
