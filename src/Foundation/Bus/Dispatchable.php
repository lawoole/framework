<?php
namespace Lawoole\Foundation\Bus;

trait Dispatchable
{
    /**
     * 分发任务
     *
     * @return \Lawoole\Foundation\Bus\PendingDispatch
     */
    public static function dispatch()
    {
        return new PendingDispatch(new static(...func_get_args()));
    }

    /**
     * 设置任务链中的后继任务
     *
     * @param array $chain
     *
     * @return \Lawoole\Foundation\Bus\PendingChain
     */
    public static function withChain($chain)
    {
        return new PendingChain(get_called_class(), $chain);
    }
}
