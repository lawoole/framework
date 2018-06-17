<?php
namespace Lawoole\Homer\Serialize;

class IgbinarySerializer extends Serializer
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
        return igbinary_serialize($message);
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
        return igbinary_unserialize($data);
    }
}
