<?php
namespace Lawoole\Foundation\Exceptions;

use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class MaintenanceModeException extends ServiceUnavailableHttpException
{
    /**
     * 网站维护开始时间
     *
     * @var \Illuminate\Support\Carbon
     */
    public $wentDownAt;

    /**
     * 再次重试时间
     *
     * @var int
     */
    public $retryAfter;

    /**
     * 将要恢复时间
     *
     * @var \Illuminate\Support\Carbon
     */
    public $willBeAvailableAt;

    /**
     * 创建维护异常
     *
     * @param int $time
     * @param int $retryAfter
     * @param string $message
     */
    public function __construct($time, $retryAfter = null, $message = null)
    {
        parent::__construct($retryAfter, $message);

        $this->wentDownAt = Carbon::createFromTimestamp($time);

        if ($retryAfter) {
            $this->retryAfter = $retryAfter;
            $this->willBeAvailableAt = Carbon::createFromTimestamp($time)->addSeconds($this->retryAfter);
        }
    }
}
