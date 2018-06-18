<?php
namespace Lawoole\Homer\Serialize\Serializations;

use Lawoole\Homer\Serialize\Maker;

class ArraySerialization extends Serialization
{
    /**
     * The serialized array values.
     *
     * @var array|\ArrayAccess
     */
    protected $values;

    /**
     * Create a array serialization instance.
     *
     * @param array|\ArrayAccess $array
     */
    public function __construct($array)
    {
        $this->values = array_map(function ($value) {
            return Maker::make($value);
        }, $array);
    }

    /**
     * Recover the array from the serialization.
     *
     * @return array|\ArrayAccess
     */
    public function recover()
    {
        return array_map(function ($value) {
            return $value instanceof Serialization ? $value->recover() : $value;
        }, $this->values);
    }
}
