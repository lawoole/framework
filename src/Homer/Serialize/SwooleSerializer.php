<?php
namespace Lawoole\Homer\Serialize;

use Swoole\Serialize;

class SwooleSerializer extends Serializer
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
        return Serialize::pack($message);
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
        return Serialize::unpack($data);
    }
}
