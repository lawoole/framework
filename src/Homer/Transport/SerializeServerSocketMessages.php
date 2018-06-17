<?php
namespace Lawoole\Homer\Transport;

trait SerializeServerSocketMessages
{
    /**
     * The data serializer factory.
     *
     * @var \Lawoole\Homer\Serialize\Factory
     */
    protected $serializerFactory;

    /**
     * Get the serializer for the server socket.
     *
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     *
     * @return \Lawoole\Homer\Serialize\Serializer
     */
    protected function getSerializer($serverSocket)
    {
        return $this->serializerFactory->serializer(
            $serverSocket->getConfig('serializer') ?: $this->getDefaultSerializer()
        );
    }

    /**
     * Get default serialize type.
     *
     * @return string
     */
    abstract protected function getDefaultSerializer();
}
