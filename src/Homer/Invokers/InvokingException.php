<?php
namespace Lawoole\Homer\Invokers;

use Lawoole\Homer\HomerException;

class InvokingException extends HomerException
{
    /**
     * Exception type: Unknown exception.
     */
    const UNKNOWN = 0;

    /**
     * Exception type: Business exception.
     */
    const BUSINESS = 1;

    /**
     * Return whether the exception is a business exception.
     *
     * @return bool
     */
    public function isBusiness()
    {
        return $this->getCode() == static::BUSINESS;
    }
}
