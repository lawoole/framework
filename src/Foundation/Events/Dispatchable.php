<?php
namespace Lawoole\Foundation\Events;

trait Dispatchable
{
    /**
     * 分发事件
     */
    public static function dispatch()
    {
        return event(new static(...func_get_args()));
    }

    /**
     * 广播事件
     *
     * @return \Illuminate\Broadcasting\PendingBroadcast
     */
    public static function broadcast()
    {
        return broadcast(new static(...func_get_args()));
    }
}
