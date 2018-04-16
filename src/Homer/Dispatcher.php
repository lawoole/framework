<?php
namespace Lawoole\Homer;

use Illuminate\Support\Facades\Log;
use Lawoole\Homer\Invokers\Invoker;

class Dispatcher
{
    /**
     * 调用器
     *
     * @var \Lawoole\Homer\Invokers\Invoker[]
     */
    protected $invokers = [];

    /**
     * 暴露服务
     *
     * @param \Lawoole\Homer\Invokers\Invoker $invoker
     */
    public function export(Invoker $invoker)
    {
        $interface = $invoker->getInterface();

        if (isset($this->invokers[$interface])) {
            return;
        }

        Log::channel('homer')->info("Service {$interface} has been exported.");

        $this->invokers[$interface] = $invoker;
    }

    /**
     * 调度调用
     *
     * @param \Lawoole\Homer\Invocation $invocation
     *
     * @return \Lawoole\Homer\Invokers\Invoker
     */
    public function dispatch(Invocation $invocation)
    {
        $interface = $invocation->getInterface();

        if (isset($this->invokers[$interface])) {
            return $this->invokers[$interface];
        }

        throw new HomerException("No invoker found for {$interface}.");
    }
}