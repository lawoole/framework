<?php
namespace Lawoole\Homer;

use Illuminate\Contracts\Container\Container;
use Lawoole\Homer\Concerns\HasAttachments;

class Context
{
    use HasAttachments;

    /**
     * 容器
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * 当前调用对象
     *
     * @var \Lawoole\Homer\Invocation
     */
    protected $invocation;

    /**
     * 创建调用上下文
     *
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * 获得容器
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * 获得当前调用
     *
     * @return \Lawoole\Homer\Invocation
     */
    public function getInvocation()
    {
        return $this->invocation;
    }

    /**
     * 设置调用
     *
     * @param \Lawoole\Homer\Invocation $invocation
     */
    public function setInvocation(Invocation $invocation)
    {
        $this->invocation = $invocation;

        $this->setAttachments($invocation->getAttachments());
    }

    /**
     * 清除当前调用
     */
    public function forgetInvocation()
    {
        $this->invocation = null;

        $this->setAttachments([]);
    }
}
