<?php
namespace Lawoole\Homer;

use RuntimeException;
use Throwable;

class HomerException extends RuntimeException
{
    /**
     * 创建 Homer 异常
     *
     * @param string $message
     * @param \Throwable $previous
     */
    public function __construct($message = '', Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
