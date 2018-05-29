<?php
namespace Lawoole\Homer\Components;

use Illuminate\Support\Arr;
use Lawoole\Homer\Context;
use Lawoole\Homer\Invokers\RemoteInvoker;
use Lawoole\Homer\Proxy;
use Lawoole\Homer\Transport\ClientFactory;
use LogicException;

class ReferenceComponent extends Component
{
    /**
     * 调用上下文
     *
     * @var \Lawoole\Homer\Context
     */
    protected $context;

    /**
     * 客户端工厂
     *
     * @var \Lawoole\Homer\Transport\ClientFactory
     */
    protected $clientFactory;

    /**
     * 调用代理
     *
     * @var \Lawoole\Homer\Proxy
     */
    protected $proxy;

    /**
     * 设置调用上下文
     *
     * @param \Lawoole\Homer\Context $context
     *
     * @return $this
     */
    public function setContext(Context $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * 设置客户端工厂
     *
     * @param \Lawoole\Homer\Transport\ClientFactory $factory
     *
     * @return $this
     */
    public function setClientFactory(ClientFactory $factory)
    {
        $this->clientFactory = $factory;

        return $this;
    }

    /**
     * 引用远程服务
     */
    public function refer()
    {
        if ($this->config == null) {
            throw new LogicException('Context must be set before refer to the service.');
        }

        if ($this->clientFactory == null) {
            throw new LogicException('Client factory must be set before refer to the service.');
        }

        $identify = Arr::get($this->config, 'id', function () {
            return Arr::get($this->config, 'interface');
        });

        $this->container->instance($identify, $this->getProxy());
    }

    /**
     * 获得调用代理
     *
     * @return \Lawoole\Homer\Proxy
     */
    public function getProxy()
    {
        if ($this->proxy !== null) {
            return $this->proxy;
        }

        return $this->proxy = $this->createProxy();
    }

    /**
     * 创建调用代理
     *
     * @return \Lawoole\Homer\Proxy
     */
    protected function createProxy()
    {
        $config = $this->config;

        $client = $this->clientFactory->getClient(
            Arr::pull($config, 'client')
        );

        $interface = Arr::pull($config, 'interface');

        $invoker = new RemoteInvoker($this->context, $client, $interface, $config);

        return new Proxy($invoker);
    }
}
