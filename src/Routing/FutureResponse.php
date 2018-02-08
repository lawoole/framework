<?php
namespace Lawoole\Routing;

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
     * 设置请求管理器
     *
     * @param \Lawoole\Routing\RequestManager $requestManager
     */
    public function setRequestManager(RequestManager $requestManager)
    {
        $this->requestManager = $requestManager;
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
}
