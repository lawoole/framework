<?php
namespace Lawoole\Foundation\Bus;

class PendingChain
{
    /**
     * 任务名
     *
     * @var string
     */
    public $class;

    /**
     * 任务链
     *
     * @var array
     */
    public $chain;

    /**
     * 创建待推送链
     *
     * @param string $class
     * @param array $chain
     */
    public function __construct($class, $chain)
    {
        $this->class = $class;
        $this->chain = $chain;
    }

    /**
     * 分发任务
     *
     * @return \Lawoole\Foundation\Bus\PendingDispatch
     */
    public function dispatch()
    {
        return (new PendingDispatch(
            new $this->class(...func_get_args())
        ))->chain($this->chain);
    }
}
