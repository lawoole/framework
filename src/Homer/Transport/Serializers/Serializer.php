<?php
namespace Lawoole\Homer\Transport\Serializers;

abstract class Serializer
{
    /**
     * 序列化数据
     *
     * @param mixed $message
     *
     * @return string
     */
    abstract public function serialize($message);

    /**
     * 反序列化数据
     *
     * @param string $data
     *
     * @return mixed
     */
    abstract public function unserialize($data);
}