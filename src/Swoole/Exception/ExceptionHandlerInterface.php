<?php
namespace Lawoole\Swoole\Exception;

use Throwable;

interface ExceptionHandlerInterface
{
    /**
     * 处理异常
     *
     * @param \Throwable $throwable
     */
    public function handleException(Throwable $throwable);
}
