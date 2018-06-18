<?php
namespace Lawoole\Homer\Calling;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Lawoole\Homer\Calling\Invokers\Invoker;
use ReflectionClass;
use Throwable;

class ProxyFactory
{
    /**
     * The proxy code stub.
     *
     * @var string
     */
    protected $proxyStub;

    /**
     * Get a invoking proxy instance.
     *
     * @param \Lawoole\Homer\Calling\Invokers\Invoker $invoker
     *
     * @return mixed
     */
    public function proxy(Invoker $invoker)
    {
        $interface = $invoker->getInterface();

        $className = $this->generateClassName($interface);

        $code = $this->populateStub($this->getStub(), $className, $interface);

        $this->load($className, $code);

        return $this->createProxyInstance($className, $interface, $invoker);
    }

    /**
     * Create a invoking proxy instance.
     *
     * @param string $className
     * @param string $interface
     * @param \Lawoole\Homer\Calling\Invokers\Invoker $invoker
     *
     * @return mixed
     */
    protected function createProxyInstance($className, $interface, Invoker $invoker)
    {
        $className = $this->getFullClassName($className);

        return new $className($interface, $invoker);
    }

    /**
     * Load the proxy class.
     *
     * @param string $className
     * @param string $code
     */
    protected function load($className, $code)
    {
        if (class_exists($this->getFullClassName($className), false)) {
            return;
        }

        eval('?>'.$code);
    }

    /**
     * Get the class name with namespace.
     *
     * @param string $className
     *
     * @return string
     */
    protected function getFullClassName($className)
    {
        return '\\Lawoole\\Homer\\Calling\\Proxy\\'.$className;
    }

    /**
     * Populate the place-holders in the proxy stub.
     *
     * @param string $stub
     * @param string $className
     * @param string $interface
     *
     * @return string
     */
    protected function populateStub($stub, $className, $interface)
    {
        $stub = str_replace('ProxyClass', $className, $stub);
        $stub = str_replace('ProxyInterface', '\\'.$interface, $stub);

        $stub = str_replace('// ## Implemented Methods ##', $this->getMethodDefinitions($interface), $stub);

        return $stub;
    }

    /**
     * Get interface's method definitions.
     *
     * @param string $interface
     *
     * @return string
     */
    protected function getMethodDefinitions($interface)
    {
        $definitions = [];

        try {
            $reflection = new ReflectionClass($interface);

            if (!$reflection->isInterface()) {
                throw new InvalidArgumentException("{$interface} must be an interface.");
            }

            foreach ($reflection->getMethods() as $method) {
                $definition = 'public function ';

                $definition .= $method->getName();
                $definition .= $this->renderMethodParameters($method);
                $definition .= $this->renderMethodReturnType($method);
                $definition .= $this->renderMethodBody($method);

                $definitions[] = $definition;
            }
        } catch (Throwable $e) {
//            Log::channel('homer')->warning("Reflect {$interface} failed.", [
//                'exception' => $e,
//            ]);

            throw $e;
        }

        return implode("\n\n", $definitions);
    }

    /**
     * Render method parameters definition.
     *
     * @param \ReflectionMethod $method
     *
     * @return string
     */
    protected function renderMethodParameters($method)
    {
        $parameters = [];

        foreach ($method->getParameters() as $parameter) {
            if ($parameter->isPassedByReference()) {
                throw new InvalidArgumentException(sprintf(
                    'Parameter [%s] in %s->%s cannot declared as a reference.',
                    $parameter->getName(), $method->getDeclaringClass(), $method->getName()
                ));
            }

            $definition = $this->renderParameterTypeHint($parameter);
            $definition .= $parameter->isVariadic() ? '...' : '';
            $definition .= '$'.$parameter->getName();

            if (!$parameter->isVariadic()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $definition .= ' = '.var_export($parameter->getDefaultValue(), true);
                } elseif ($parameter->isOptional()) {
                    $definition .= ' = null';
                }
            }

            $parameters[] = $definition;
        }

        return '('.implode(', ', $parameters).')';
    }

    /**
     * Render method parameter's type hint.
     *
     * @param \ReflectionParameter $parameter
     *
     * @return string
     */
    protected function renderParameterTypeHint($parameter)
    {
        $typeHint = $parameter->hasType() ? (string) $parameter->getType() : '';

        $typeHint = trim($typeHint);

        if ($typeHint) {
            // If the type is not a built in types, we should add a leading slash
            // in the type hint be make the class name not confusing.
            if (
                !in_array($typeHint, [
                    'self', 'array', 'callable', 'bool', 'float', 'int', 'string',
                    'object', 'iterable', 'void'
                ])
            ) {
                $typeHint = '\\'.$typeHint;
            }

            if ($parameter->allowsNull()) {
                $typeHint = '?'.$typeHint;
            }

            $typeHint .= ' ';
        }

        return $typeHint;
    }

    /**
     * Render method return type definition.
     *
     * @param \ReflectionMethod $method
     *
     * @return string
     */
    protected function renderMethodReturnType($method)
    {
        $type = $method->getReturnType();

        return $type ? ': '.$type : '';
    }

    /**
     * Render method body definition.
     *
     * @param string $method
     *
     * @return string
     */
    protected function renderMethodBody($method)
    {
        $body = '{';
        $body .= '$arguments = func_get_args();';
        $body .= '$result = $this->__call(__FUNCTION__, $arguments);';

        if ($method->getReturnType() !== 'void') {
            $body .= 'return $result;';
        }

        $body .= '}';

        return $body;
    }

    /**
     * Generate a class name for the proxy.
     *
     * @param string $interface
     *
     * @return string
     */
    protected function generateClassName($interface)
    {
        return 'ProxyFor'.class_basename($interface).'Implement'.Str::random(16);
    }

    /**
     * Get the proxy stub file.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->proxyStub == null) {
            $this->proxyStub = file_get_contents($this->stubPath().'/proxy.stub');
        }

        return $this->proxyStub;
    }

    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    protected function stubPath()
    {
        return __DIR__.'/stubs';
    }
}