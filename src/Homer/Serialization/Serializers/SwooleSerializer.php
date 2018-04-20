<?php
namespace Lawoole\Homer\Serialization\Serializers;

use Swoole\Serialize;

class SwooleSerializer extends Serializer
{
    /**
     * 序列化数据
     *
     * @param mixed $message
     *
     * @return string
     */
    public function serialize($message)
    {
        return Serialize::pack($message);
    }

    /**
     * 反序列化数据
     *
     * @param string $data
     *
     * @return mixed
     */
    public function unserialize($data)
    {
        return Serialize::unpack($data);
    }
}