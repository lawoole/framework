<?php
namespace Lawoole\Homer\Serialize\Serializations;

use Lawoole\Homer\Serialize\Maker;

class ObjectSerialization extends Serialization
{
    /**
     * The serialized object.
     *
     * @var \stdClass
     */
    protected $values;

    /**
     * Create a object serialization instance.
     *
     * @param \stdClass $instance
     */
    public function __construct($instance)
    {
        foreach (get_object_vars($instance) as $key => $value) {
            $instance->$key = Maker::make($value);
        }

        $this->values = $instance;
    }

    /**
     * Recover the instance from the serialization.
     *
     * @return \stdClass
     */
    public function recover()
    {
        $instance = $this->values;

        foreach (get_object_vars($instance) as $key => $value) {
            $instance->$key = $value instanceof Serialization ? $value->recover() : $value;
        }

        return $instance;
    }
}