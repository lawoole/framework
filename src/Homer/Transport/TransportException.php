<?php
namespace Lawoole\Homer\Transport;

use Lawoole\Homer\HomerException;

class TransportException extends HomerException
{
    /**
     * Exception exception: Unknown exception.
     */
    const UNKNOWN = 0;

    /**
     * Exception exception: Connection exception.
     */
    const CONNECTION = 1;

    /**
     * Exception exception: Network timeout exception.
     */
    const TIMEOUT = 2;

    /**
     * Exception exception: Data serialization exception.
     */
    const SERIALIZATION = 3;

    /**
     * Exception exception: Remote processing exception.
     */
    const REMOTE = 4;

    /**
     * Return whether the exception is a connection exception.
     *
     * @return bool
     */
    public function isConnection()
    {
        return $this->getCode() == static::CONNECTION;
    }

    /**
     * Return whether the exception is a network timeout exception.
     *
     * @return bool
     */
    public function isTimeout()
    {
        return $this->getCode() == static::TIMEOUT;
    }

    /**
     * Return whether the exception is a data serialization exception.
     *
     * @return bool
     */
    public function isSerialization()
    {
        return $this->getCode() == static::SERIALIZATION;
    }

    /**
     * Return whether the exception is a remote processing exception.
     *
     * @return bool
     */
    public function isRemote()
    {
        return $this->getCode() == static::REMOTE;
    }
}
