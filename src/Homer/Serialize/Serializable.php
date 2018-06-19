<?php
namespace Lawoole\Homer\Serialize;

use Lawoole\Homer\Serialize\Serializations\Serialization;
use ReflectionClass;

trait Serializable
{
    /**
     * Prepare for serialization.
     *
     * @return array
     */
    public function __sleep()
    {
        $properties = [];

        $reflection = new ReflectionClass($this);

        foreach ($reflection->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $property->setAccessible(true);

            $value = $property->getValue($this);

            $property->setValue($this, Maker::make($value));

            $properties[] = $property->getName();
        }

        return $properties;
    }

    /**
     * Recover from serialization.
     */
    public function __wakeup()
    {
        $reflection = new ReflectionClass($this);

        foreach ($reflection->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $property->setAccessible(true);

            $value = $property->getValue($this);

            if ($value instanceof Serialization) {
                $property->setValue($this, $value->recover());
            }
        }
    }
}
