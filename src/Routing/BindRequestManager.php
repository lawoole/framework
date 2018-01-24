<?php
namespace Lawoole\Routing;

use Illuminate\Contracts\Container\Container;

trait BindRequestManager
{
    /**
     * 请求管理器编号
     *
     * @var string
     */
    protected $requestManagerId;

    /**
     * 绑定请求管理器
     *
     * @param \Lawoole\Routing\RequestManager $requestManager
     *
     * @return $this
     */
    public function withRequestManager(RequestManager $requestManager)
    {
        $this->setRequestManagerId($requestManager->getId());

        return $this;
    }

    /**
     * 设置请求管理器编号
     *
     * @param string $requestManagerId
     */
    public function setRequestManagerId($requestManagerId)
    {
        $this->requestManagerId = $requestManagerId;
    }

    /**
     * 获得请求管理器编号
     *
     * @return string
     */
    public function getRequestManagerId()
    {
        return $this->requestManagerId;
    }

    /**
     * 获得管理器
     *
     * @param \Illuminate\Contracts\Container\Container $container
     *
     * @return \Lawoole\Routing\RequestManager
     */
    public function getRequestManager(Container $container)
    {
        return RequestManager::getManager($container, $this->requestManagerId);
    }
}
