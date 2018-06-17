<?php
namespace Lawoole\Homer\Serialize;

use InvalidArgumentException;

class Factory
{
    /**
     * All resolved serializers.
     *
     * @var array
     */
    protected $serializers = [];

    /**
     * Get the serializer by type.
     *
     * @param string $type
     *
     * @return \Lawoole\Homer\Serialize\Serializer
     */
    public function serializer($type = null)
    {
        $type = $type ?? 'php';

        if (isset($this->serializers[$type])) {
            return $this->serializers[$type];
        }

        return $this->serializers[$type] = $this->resolve($type);
    }

    /**
     * Resolve the serializer by type.
     *
     * @param string $type
     *
     * @return \Lawoole\Homer\Serialize\Serializer
     */
    protected function resolve($type)
    {
        switch ($type) {
            case 'php':
                return new NativeSerializer;
            case 'swoole':
                return new SwooleSerializer;
            case 'igbinary':
                return new IgbinarySerializer;
            case 'msgpack':
                return new MessagePackSerializer;
            default:
                throw new InvalidArgumentException("Serializer type [{$type}] is not supported.");
        }
    }
}
