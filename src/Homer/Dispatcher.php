<?php
namespace Lawoole\Homer;

use Illuminate\Support\Facades\Log;
use Lawoole\Homer\Invokers\Invoker;
use Throwable;

class Dispatcher
{
    /**
     * 调用器
     *
     * @var \Lawoole\Homer\Invokers\Invoker[]
     */
    protected $invokers = [];

    /**
     * 调用上下文
     *
     * @var \Lawoole\Homer\Context
     */
    protected $context;

    /**
     * 创建调用上下文
     *
     * @param \Lawoole\Homer\Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * 暴露调用器
     *
     * @param \Lawoole\Homer\Invokers\Invoker $invoker
     */
    public function exportInvoker(Invoker $invoker)
    {
        $interface = $invoker->getInterface();

        if (isset($this->invokers[$interface])) {
            return;
        }

        Log::channel('homer')->info("Service {$interface} has been exported.");

        $this->invokers[$interface] = $invoker;
    }

    /**
     * 处理消息
     *
     * @param mixed $message
     *
     * @return mixed
     */
    public function handleMessage($message)
    {
        try {
            if ($message instanceof Invocation) {
                return $this->handleInvocation($message);
            }

            return $message;
        } catch (HomerException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::channel('homer')->warning($e->getMessage(), [
                'exception' => $e
            ]);

            throw $e;
        }
    }

    /**
     * 处理调用
     *
     * @param \Lawoole\Homer\Invocation $invocation
     *
     * @return \Lawoole\Homer\Result
     */
    protected function handleInvocation(Invocation $invocation)
    {
        $invoker = $this->dispatchInvocation($invocation);

        $this->context->setInvocation($invocation);

        $result = $invoker->invoke($invocation);

        $this->context->forgetInvocation();

        return $result;
    }

    /**
     * 调度调用
     *
     * @param \Lawoole\Homer\Invocation $invocation
     *
     * @return \Lawoole\Homer\Invokers\Invoker
     */
    protected function dispatchInvocation(Invocation $invocation)
    {
        $interface = $invocation->getInterface();

        if (isset($this->invokers[$interface])) {
            return $this->invokers[$interface];
        }

        throw new HomerException("No invoker found for {$interface}.");
    }
}
