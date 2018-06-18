<?php
namespace Lawoole\Homer\Calling;

use Illuminate\Support\Facades\Log;
use Lawoole\Homer\Calling\Invokers\Invoker;
use Lawoole\Homer\HomerException;
use Throwable;

class Dispatcher
{
    /**
     * All registered invokers.
     *
     * @var \Lawoole\Homer\Calling\Invokers\Invoker[]
     */
    protected $invokers = [];

    /**
     * Export the invoker.
     *
     * @param \Lawoole\Homer\Calling\Invokers\Invoker $invoker
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
     * Handler the invoking request.
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
     * Handle the invocation.
     *
     * @param \Lawoole\Homer\Calling\Invocation $invocation
     *
     * @return \Lawoole\Homer\Calling\Result
     */
    protected function handleInvocation(Invocation $invocation)
    {
        $invoker = $this->dispatchInvocation($invocation);

        return $invoker->invoke($invocation);
    }

    /**
     * Dispatch the invocation.
     *
     * @param \Lawoole\Homer\Calling\Invocation $invocation
     *
     * @return \Lawoole\Homer\Calling\Invokers\Invoker
     */
    protected function dispatchInvocation(Invocation $invocation)
    {
        $interface = $invocation->getInterface();

        if (isset($this->invokers[$interface])) {
            return $this->invokers[$interface];
        }

        throw new CallingException("No invoker found for {$interface}.");
    }
}
