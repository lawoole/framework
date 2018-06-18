<?php
namespace Lawoole\Homer\Serialize;

use Closure;
use DateTime;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Arr;
use Lawoole\Homer\Serialize\Serializations\ArraySerialization;
use Lawoole\Homer\Serialize\Serializations\ContainerSerialization;
use Lawoole\Homer\Serialize\Serializations\DateTimeSerialization;
use Lawoole\Homer\Serialize\Serializations\ObjectSerialization;
use Lawoole\Homer\Serialize\Serializations\ThrowableSerialization;
use Lawoole\Homer\Serialize\Serializations\ValidatorSerialization;
use stdClass;
use Throwable;

class Maker
{
    /**
     * Get the value for serialization.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function make($value)
    {
        if (is_resource($value)) {
            $value = null;
        } elseif (Arr::accessible($value)) {
            $value = new ArraySerialization($value);
        } elseif ($value instanceof stdClass) {
            $value = new ObjectSerialization($value);
        } elseif ($value instanceof Container) {
            $value = new ContainerSerialization($value);
        } elseif ($value instanceof DateTime) {
            $value = new DateTimeSerialization($value);
        } elseif ($value instanceof Validator) {
            $value = new ValidatorSerialization($value);
        } elseif ($value instanceof Throwable) {
            $value = new ThrowableSerialization($value);
        } elseif ($value instanceof Closure) {
            $value = null;
        }

        return $value;
    }
}
