<?php
namespace Lawoole\Http;

use Symfony\Component\HttpFoundation\Response;

class FutureResponse extends Response
{
    /**
     * 请求管理器
     *
     * @var \Lawoole\Routing\RequestManager
     */
    protected $requestManager;

    /**
     * 处理超时
     *
     * @var float
     */
    protected $timeout;

    /**
     * 创建延迟响应
     *
     * @param \Lawoole\Routing\RequestManager $requestManager
     * @param int $timeout
     */
    public function __construct(RequestManager $requestManager, $timeout = 0)
    {
        parent::__construct('', 202);

        $this->requestManager = $requestManager;
        $this->timeout = $timeout;
    }

    /**
     * 获得请求管理器
     *
     * @return \Lawoole\Routing\RequestManager
     */
    public function getRequestManager()
    {
        return $this->requestManager;
    }

    /**
     * 设置处理超时
     *
     * @param float $timeout
     *
     * @return \Lawoole\Routing\FutureResponse
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * 获得处理超时
     *
     * @return float
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
}
