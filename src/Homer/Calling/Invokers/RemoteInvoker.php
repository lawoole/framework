<?php
namespace Lawoole\Homer\Calling\Invokers;

use Illuminate\Support\Facades\Log;
use Lawoole\Homer\Calling\CallingException;
use Lawoole\Homer\Calling\Invocation;
use Lawoole\Homer\Calling\Result;
use Lawoole\Homer\Context;
use Lawoole\Homer\Transport\Client;
use Throwable;

class RemoteInvoker extends Invoker
{
    /**
     * The invoking context instance.
     *
     * @var \Lawoole\Homer\Context
     */
    protected $context;

    /**
     * The calling client.
     *
     * @var \Lawoole\Homer\Transport\Client
     */
    protected $client;

    /**
     * Create a remote invoker instance.
     *
     * @param \Lawoole\Homer\Context $context
     * @param \Lawoole\Homer\Transport\Client $client
     * @param string $interface
     * @param array $options
     */
    public function __construct(Context $context, Client $client, $interface, array $options = [])
    {
        parent::__construct($interface, $options);

        $this->context = $context;
        $this->client = $client;
    }

    /**
     * Do invoking and get the result.
     *
     * @param \Lawoole\Homer\Calling\Invocation $invocation
     *
     * @return \Lawoole\Homer\Calling\Result
     */
    protected function doInvoke(Invocation $invocation)
    {
        $invocation->setAttachments($this->context->getAttachments());

        $startTime = microtime(true);

        try {
            $result = $this->client->request($invocation);

            if (!$result instanceof Result) {
                Log::channel('homer')->warning('Server response an error message', [
                    'result' => (string) $result
                ]);

                throw new CallingException('The invoke result must instance of Result, '
                    .class_basename($result).' given.');
            }
        } catch (Throwable $e) {
            $result = $this->createExceptionResult($e);
        }

        if ($this->isDebug()) {
            $this->logInvoking($invocation, $result, $this->getElapsedTime($startTime));
        }

        return $result;
    }

    /**
     * Log the invocation.
     *
     * @param \Lawoole\Homer\Calling\Invocation $invocation
     * @param \Lawoole\Homer\Calling\Result $result
     * @param float $time
     */
    protected function logInvoking(Invocation $invocation, Result $result, $time = null)
    {
        Log::channel('homer')->debug(
            sprintf('%s %5.2fms %s->%s',
                $result->hasException() ? 'Success' : 'Failure', $time, $invocation->getInterface(),
                $invocation->getMethod()
            ),
            [
                'during'      => $time,
                'arguments'   => $invocation->getArguments(),
                'attachments' => $invocation->getAttachments(),
                'result'      => $result->getValue(),
                'exception'   => $result->getException(),
            ]
        );
    }

    /**
     * Calculate the time elapsed.
     *
     * @param int $start
     *
     * @return float
     */
    protected function getElapsedTime($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }
}
