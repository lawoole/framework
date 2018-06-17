<?php
namespace Lawoole\Homer\Serialize;

class NativeSerializer extends Serializer
{
    /**
     * Serialize the data.
     *
     * @param mixed $message
     *
     * @return string
     */
    public function doSerialize($message)
    {
        return serialize($message);
    }

    /**
     * Deserialize the data.
     *
     * @param string $data
     *
     * @return mixed
     */
    public function doDeserialize($data)
    {
        return unserialize($data);
    }
}
