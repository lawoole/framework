<?php
namespace Lawoole\Homer\Serialize;

abstract class Serializer
{
    /**
     * Serialize the data.
     *
     * @param mixed $message
     *
     * @return string
     */
    public function serialize($message)
    {
        return $this->doSerialize($message);
    }

    /**
     * Deserialize the data.
     *
     * @param string $data
     *
     * @return mixed
     */
    public function deserialize($data)
    {
        return $this->doDeserialize($data);
    }

    /**
     * Serialize the data.
     *
     * @param mixed $message
     *
     * @return string
     */
    abstract public function doSerialize($message);

    /**
     * Deserialize the data.
     *
     * @param string $data
     *
     * @return mixed
     */
    abstract public function doDeserialize($data);
}
