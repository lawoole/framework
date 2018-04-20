<?php
namespace Lawoole\Homer\Serialization\Serializers;

use InvalidArgumentException;

class SerializerFactory
{
    /**
     * 默认序列化方式
     */
    const DEFAULT_SERIALIZER = 'php';

    /**
     * 序列化工具集合
     *
     * @var array
     */
    protected $serializers = [];

    /**
     * 获得序列化工具
     *
     * @param string $type
     *
     * @return \Lawoole\Homer\Serialization\Serializers\Serializer
     */
    public function getSerializer($type = null)
    {
        if ($type === null) {
            $type = self::DEFAULT_SERIALIZER;
        }

        if (isset($this->serializers[$type])) {
            return $this->serializers[$type];
        }

        return $this->serializers[$type] = $this->createSerializer($type);
    }

    /**
     * 创建序列化工具
     *
     * @param string $type
     *
     * @return \Lawoole\Homer\Serialization\Serializers\Serializer
     */
    protected function createSerializer($type)
    {
        switch ($type) {
            case 'php':
                return new NativeSerializer;
            case 'swoole':
                return new SwooleSerializer;
            default:
                throw new InvalidArgumentException("Serializer type [{$type}] is not supported.");
        }
    }
}
