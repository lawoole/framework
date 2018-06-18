<?php
namespace Lawoole\Homer\Serialize\Serializations;

abstract class Serialization
{
    /**
     * Recover the instance from the serialization.
     *
     * @return mixed
     */
    abstract public function recover();
}
