<?php
namespace Lawoole\Homer\Serialize;

class MessagePackSerializer extends Serializer
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
        return msgpack_pack($message);
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
        return msgpack_unpack($data);
    }
}
