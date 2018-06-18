<?php
namespace Lawoole\Homer\Components;

use Illuminate\Support\Arr;
use Lawoole\Homer\Calling\Invokers\RemoteInvoker;
use Lawoole\Homer\Calling\ProxyFactory;
use Lawoole\Homer\Context;
use Lawoole\Homer\Transport\ClientFactory;
use LogicException;

class ReferenceComponent extends Component
{
    /**
     * The invoking context instance.
     *
     * @var \Lawoole\Homer\Context
     */
    protected $context;

    /**
     * The proxy factory instance.
     *
     * @var \Lawoole\Homer\Calling\ProxyFactory
     */
    protected $proxyFactory;

    /**
     * The client instance.
     *
     * @var \Lawoole\Homer\Transport\Client
     */
    protected $clientFactory;

    /**
     * The instance of calling proxy.
     *
     * @var mixed
     */
    protected $proxy;

    /**
     * The invoking context instance.
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
     * Set the client factory instance.
     *
     * @param \Lawoole\Homer\Transport\ClientFactory $clientFactory
     *
     * @return $this
     */
    public function setClientFactory(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;

        return $this;
    }

    /**
     * Set the proxy factory instance.
     *
     * @param \Lawoole\Homer\Calling\ProxyFactory $proxyFactory
     *
     * @return $this
     */
    public function setProxyFactory(ProxyFactory $proxyFactory)
    {
        $this->proxyFactory = $proxyFactory;

        return $this;
    }

    /**
     * Reference the interface.
     */
    public function refer()
    {
        if ($this->context == null) {
            throw new LogicException('Context must be set before refer to the service.');
        }

        if ($this->proxyFactory == null) {
            throw new LogicException('Proxy factory must be set before refer to the service.');
        }

        if ($this->clientFactory == null) {
            throw new LogicException('Client factory must be set before refer to the service.');
        }

        $identify = $this->config['id'] ?? $this->config['interface'] ?? null;

        $this->container->instance($identify, $this->getProxy());
    }

    /**
     * Get the calling proxy of interface.
     *
     * @return mixed
     */
    public function getProxy()
    {
        if ($this->proxy !== null) {
            return $this->proxy;
        }

        return $this->proxy = $this->createProxy();
    }

    /**
     * Create the calling proxy.
     *
     * @return mixed
     */
    protected function createProxy()
    {
        $config = $this->config;

        $interface = Arr::pull($config, 'interface');

        $client = $this->clientFactory->client(Arr::pull($config, 'client'));

        $invoker = new RemoteInvoker($this->context, $client, $interface, $config);

        return $this->proxyFactory->proxy($invoker);
    }
}
