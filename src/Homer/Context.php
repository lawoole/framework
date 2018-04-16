<?php
namespace Lawoole\Homer;

use Lawoole\Homer\Concerns\HasAttachments;

class Context
{
    use HasAttachments;

    /**
     * 当前调用对象
     *
     * @var \Lawoole\Homer\Invocation
     */
    protected $invocation;

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
