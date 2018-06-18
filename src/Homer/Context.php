<?php
namespace Lawoole\Homer;

use Illuminate\Contracts\Container\Container;
use Lawoole\Homer\Calling\Invocation;
use Lawoole\Support\HasAttachments;

class Context
{
    use HasAttachments;

    /**
     * The container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * The current invocation instance.
     *
     * @var \Lawoole\Homer\Calling\Invocation
     */
    protected $invocation;

    /**
     * Create a invoking context instance.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get the container instance.
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get the current invocation instance.
     *
     * @return \Lawoole\Homer\Calling\Invocation
     */
    public function getInvocation()
    {
        return $this->invocation;
    }

    /**
     * Set the invocation instance.
     *
     * @param \Lawoole\Homer\Calling\Invocation $invocation
     */
    public function setInvocation(Invocation $invocation)
    {
        $this->invocation = $invocation;

        $this->setAttachments($invocation->getAttachments());
    }

    /**
     * Remove the invocation instance.
     */
    public function forgetInvocation()
    {
        $this->invocation = null;

        $this->setAttachments([]);
    }
}
