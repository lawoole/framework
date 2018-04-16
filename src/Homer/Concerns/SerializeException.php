<?php
namespace Lawoole\Homer\Concerns;

use Lawoole\Homer\Serialization\ExceptionSerialization;
use ReflectionClass;
use Throwable;

trait SerializeException
{
    /**
     * 准备序列化
     *
     * @return array
     */
    public function __sleep()
    {
        $reflection = new ReflectionClass($this);

        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $property->setAccessible(true);

            $value = $property->getValue($this);

            if ($value instanceof Throwable) {
                $property->setValue($this, new ExceptionSerialization($value));
            }
        }

        return array_values(array_filter(array_map(function ($property) {
            return $property->isStatic() ? null : $property->getName();
        }, $properties)));
    }

    /**
     * 从序列化中恢复
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

            if ($value instanceof ExceptionSerialization) {
                $property->setValue($this, $value->getException());
            }
        }
    }
}