<?php
namespace Lawoole\Homer\Invokers;

use Illuminate\Support\Facades\Log;
use Lawoole\Homer\Context;
use Lawoole\Homer\HomerException;
use Lawoole\Homer\Invocation;
use Lawoole\Homer\Result;
use Lawoole\Homer\Transport\Client;

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
     * 执行调用并得到调用结果
     *
     * @param \Lawoole\Homer\Invocation $invocation
     *
     * @return \Lawoole\Homer\Result
     */
    protected function doInvoke(Invocation $invocation)
    {
        $invocation->setAttachments($this->context->getAttachments());

        $result = $this->client->request($invocation);

        if (!$result instanceof Result) {
            Log::channel('homer')->warning('Server response an error message', [
                'result' => (string) $result
            ]);

            throw new InvokingException('The invoke result must instance of Result, '
                .class_basename($result).' given.');
        }

        return $result;
    }
}
