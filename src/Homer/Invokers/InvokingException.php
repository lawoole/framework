<?php
namespace Lawoole\Homer\Invokers;

use Lawoole\Homer\HomerException;

class InvokingException extends HomerException
{
    /**
     * 异常类型：未知异常
     */
    const UNKNOWN = 0;

    /**
     * 异常类型：业务异常
     */
    const BUSINESS = 1;

    /**
     * 异常类型：网络超时
     */
    const TIMEOUT = 2;

    /**
     * 异常类型：数据序列化失败
     */
    const SERIALIZATION = 3;

    /**
     * 判断异常是否为业务异常
     *
     * @return bool
     */
    public function isBusiness()
    {
        return $this->getCode() == static::BUSINESS;
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
}