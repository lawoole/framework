<?php
namespace Lawoole\Homer\Invokers;

use Illuminate\Support\Facades\Log;
use Lawoole\Homer\Context;
use Lawoole\Homer\Invocation;
use Lawoole\Homer\Result;
use Lawoole\Homer\Transport\Client;
use Throwable;

class RemoteInvoker extends Invoker
{
    /**
     * 调用上下文
     *
     * @var \Lawoole\Homer\Context
     */
    protected $context;

    /**
     * 客户端对象
     *
     * @var \Lawoole\Homer\Transport\Client
     */
    protected $client;

    /**
     * 远程调用器
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
     * 判断是否开启调试
     *
     * @return bool
     */
    protected function isDebug()
    {
        return $this->options['debug'] ?? false;
    }

    /**
     * 执行调用并得到调用结果
     *
     * @param \Lawoole\Homer\Invocation $invocation
     *
     * @return \Lawoole\Homer\Result
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

                throw new InvokingException('The invoke result must instance of Result, '
                    .class_basename($result).' given.');
            }
        } catch (Throwable $e) {
            $result = $this->createExceptionResult($e);
        }

        $this->logInvoking($invocation, $result, $this->getElapsedTime($startTime));

        return $result;
    }

    /**
     * 记录调用日志
     *
     * @param \Lawoole\Homer\Invocation $invocation
     * @param \Lawoole\Homer\Result $result
     * @param float $time
     */
    protected function logInvoking(Invocation $invocation, Result $result, $time = null)
    {
        if ($this->isDebug()) {
            $invoking = "{$invocation->getInterface()}->{$invocation->getMethod()}()";

            Log::channel('homer')->debug(sprintf('%s %5.2fms %s',
                $result->hasException() ? 'Success' : 'Failure', $time, $invoking
            ), [
                'during'      => $time,
                'method'      => $invoking,
                'arguments'   => $invocation->getArguments(),
                'attachments' => $invocation->getAttachments(),
                'result'      => $result->getValue(),
                'exception'   => $result->getException(),
            ]);
        }
    }

    /**
     * 获得逝去时间差
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
