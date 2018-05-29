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
     * 判断异常是否为业务异常
     *
     * @return bool
     */
    public function isBusiness()
    {
        return $this->getCode() == static::BUSINESS;
    }
}
