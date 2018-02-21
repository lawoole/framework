<?php
namespace Lawoole\Routing;

use Symfony\Component\HttpFoundation\Response;

class MultiBulkResponse extends Response
{
    /**
     * 发送步骤：发送响应头
     */
    const STEP_HEADER = 1;

    /**
     * 发送步骤：发送响应体
     */
    const STEP_BODY = 2;

    /**
     * 发送步骤：结束发送
     */
    const STEP_FINISH = 3;

    /**
     * 发送步骤
     *
     * @var int
     */
    protected $step;

    /**
     * 创建多步响应
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     */
    public function __construct($content = '', $status = 200, array $headers = [])
    {
        parent::__construct($content, $status, $headers);
    }

    /**
     * 设置发送步骤
     *
     * @param int $step
     *
     * @return $this
     */
    public function setStep($step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * 获得发送步骤
     *
     * @return int
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * 判断当前发送步骤
     *
     * @param int|array $steps
     *
     * @return bool
     */
    public function isStep($steps)
    {
        $steps = (array) $steps;

        return in_array($this->step, $steps);
    }

    /**
     * 发送响应
     *
     * @return $this
     */
    public function send()
    {
        if ($this->step == self::STEP_HEADER) {
            $this->sendHeaders();
        }

        $this->sendContent();

        if ($this->step == self::STEP_FINISH) {
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } elseif ('cli' !== PHP_SAPI) {
                static::closeOutputBuffers(0, true);
            }
        }

        return $this;
    }
}
