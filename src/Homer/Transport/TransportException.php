<?php
namespace Lawoole\Homer\Transport;

use Lawoole\Homer\HomerException;

class TransportException extends HomerException
{
    /**
     * 异常类型：未知异常
     */
    const UNKNOWN = 0;

    /**
     * 异常类型：连接异常
     */
    const CONNECTION = 1;

    /**
     * 异常类型：网络超时
     */
    const TIMEOUT = 2;

    /**
     * 异常类型：数据序列化失败
     */
    const SERIALIZATION = 3;

    /**
     * 异常类型：远端处理异常
     */
    const REMOTE = 4;

    /**
     * 判断异常是否为连接异常
     *
     * @return bool
     */
    public function isConnection()
    {
        return $this->getCode() == static::CONNECTION;
    }

    /**
     * 判断异常是否为网络超时
     *
     * @return bool
     */
    public function isTimeout()
    {
        return $this->getCode() == static::TIMEOUT;
    }

    /**
     * 判断异常是否为数据序列化失败
     *
     * @return bool
     */
    public function isSerialization()
    {
        return $this->getCode() == static::SERIALIZATION;
    }

    /**
     * 判断异常是否为远端处理异常
     *
     * @return bool
     */
    public function isRemote()
    {
        return $this->getCode() == static::REMOTE;
    }
}
