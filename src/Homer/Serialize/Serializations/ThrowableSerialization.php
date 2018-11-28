<?php
namespace Lawoole\Homer\Serialize\Serializations;

use Lawoole\Homer\Serialize\Maker;
use ReflectionClass;
use RuntimeException;
use Throwable;

class ThrowableSerialization extends Serialization
{
    /**
     * The class name of the exception.
     *
     * @var string
     */
    protected $class;

    /**
     * The message of the exception.
     *
     * @var string
     */
    protected $message;

    /**
     * All properties.
     *
     * @var array
     */
    protected $properties;

    /**
     * Create a exception serialization instance.
     *
     * @param \Throwable $e
     */
    public function __construct(Throwable $e)
    {
        $this->serializeThrowable($e);
    }

    /**
     * Serialize the throwable.
     *
     * @param \Throwable $e
     */
    public function serializeThrowable(Throwable $e)
    {
        $this->class = get_class($e);
        $this->message = $e->getMessage();

        $this->properties = [];

        try {
            $reflection = new ReflectionClass($this->class);

            foreach ($reflection->getProperties() as $property) {
                if ($property->isStatic()) {
                    continue;
                }

                $property->setAccessible(true);

                $name = $property->getName();

                if ($name == 'trace') {
                    continue;
                }

                $value = $property->getValue($e);

                $this->properties[$name] = Maker::make($value);
            }
        } catch (Throwable $ex) {
            //
        }
    }

    /**
     * Recover the exception from the serialization.
     *
     * @return mixed
     */
    public function recover()
    {
        if (! class_exists($this->class)) {
            return new RuntimeException($this->message);
        }

        try {
            $reflection = new ReflectionClass($this->class);

            $e = $reflection->newInstanceWithoutConstructor();

            if (! $e instanceof Throwable) {
                throw new RuntimeException("Class [{$this->class}] is not an exception.");
            }

            foreach ($reflection->getProperties() as $property) {
                $name = $property->getName();

                if (isset($this->properties[$name])) {
                    $value = $this->properties[$name];

                    $property->setAccessible(true);

                    $property->setValue($e, $value instanceof Serialization ? $value->recover() : $value);
                }
            }

            return $e;
        } catch (Throwable $ex) {
            return new RuntimeException($this->message);
        }
    }
}
