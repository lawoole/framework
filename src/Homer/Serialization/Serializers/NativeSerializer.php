<?php
namespace Lawoole\Homer\Serialization\Serializers;

class NativeSerializer extends Serializer
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
        return serialize($message);
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
        return unserialize($data);
    }
}