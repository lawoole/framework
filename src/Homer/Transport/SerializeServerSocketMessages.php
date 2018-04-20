<?php
namespace Lawoole\Homer\Transport;

trait SerializeServerSocketMessages
{
    /**
     * 序列化工具工厂
     *
     * @var \Lawoole\Homer\Serialization\Serializers\SerializerFactory
     */
    protected $serializerFactory;

    /**
     * 序列化消息
     *
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     *
     * @return \Lawoole\Homer\Serialization\Serializers\Serializer
     */
    protected function getSerializer($serverSocket)
    {
        return $this->serializerFactory->getSerializer(
            $serverSocket->getConfig('serializer') ?: $this->getDefaultSerializer()
        );
    }

    /**
     * 获得默认序列化方式
     *
     * @return string
     */
    abstract protected function getDefaultSerializer();
}