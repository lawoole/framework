<?php
namespace Lawoole\Homer\Components;

use Illuminate\Support\Arr;
use Lawoole\Homer\Context;
use Lawoole\Homer\Invokers\RemoteInvoker;
use Lawoole\Homer\Proxy;
use Lawoole\Homer\Transport\Client;
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
     * The client instance.
     *
     * @var \Lawoole\Homer\Transport\Client
     */
    protected $client;

    /**
     * The instance of calling proxy.
     *
     * @var \Lawoole\Homer\Proxy
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
     * Set the client instance.
     *
     * @param \Lawoole\Homer\Transport\Client $client
     *
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Reference the interface.
     */
    public function refer()
    {
        if ($this->config == null) {
            throw new LogicException('Context must be set before refer to the service.');
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
     * Create the
     *
     * @return \Lawoole\Homer\Proxy
     */
    protected function createProxy()
    {
        $config = $this->config;

        $interface = Arr::pull($config, 'interface');

        $invoker = new RemoteInvoker($this->context, $this->client, $interface, $config);

        return new Proxy($invoker);
    }
}
