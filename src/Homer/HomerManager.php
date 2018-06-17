<?php
namespace Lawoole\Homer;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Lawoole\Contracts\Homer\Context as ContextContract;
use Lawoole\Contracts\Homer\Homer as HomerContract;
use Lawoole\Contracts\Homer\Registrar;
use Lawoole\Homer\Components\ReferenceComponent;
use Lawoole\Homer\Components\ServiceComponent;
use Lawoole\Homer\Transport\ClientFactory;

class HomerManager implements HomerContract, Registrar
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The config of Homer.
     *
     * @var array
     */
    protected $config;

    /**
     * Whether the Homer has been boot.
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * The invoking context instance.
     *
     * @var \Lawoole\Homer\Context
     */
    protected $context;

    /**
     * The invoking dispatcher.
     *
     * @var \Lawoole\Homer\Dispatcher
     */
    protected $dispatcher;

    /**
     * The client factory instance.
     *
     * @var \Lawoole\Homer\Transport\ClientFactory
     */
    protected $clientFactory;

    /**
     * Create a Homer manager instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Lawoole\Contracts\Homer\Context $context
     * @param \Lawoole\Homer\Dispatcher $dispatcher
     * @param \Lawoole\Homer\Transport\ClientFactory $clientFactory
     * @param array $config
     */
    public function __construct(Application $app, ContextContract $context, Dispatcher $dispatcher,
        ClientFactory $clientFactory, array $config = [])
    {
        $this->app = $app;
        $this->config = $config;
        $this->context = $context;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Boot the Homer.
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        $this->resolveServices();

        $this->resolveReferences();
    }

    /**
     * Resolve and register all configured references.
     */
    protected function resolveReferences()
    {
        $references = $this->config['references'] ?? [];

        if (!is_array($references) || empty($references)) {
            return;
        }

        foreach ($references as $config) {
            $this->resolveReference($config);
        }
    }

    /**
     * Register a reference in the Homer.
     *
     * @param array $config
     *
     * @return mixed
     */
    public function resolveReference(array $config)
    {
        $config = $this->normalizeClientConfig($config);

        $client = $this->clientFactory->client(Arr::pull($config, 'client'));

        return (new ReferenceComponent($this->app, $config))
            ->setContext($this->context)
            ->setClient($client)
            ->refer();
    }

    /**
     * Normalize the client section in reference configurations.
     *
     * @param array $config
     *
     * @return array
     */
    protected function normalizeClientConfig(array $config)
    {
        if (!isset($config['client'])) {
            $config['client'] = ['url' => $config['url']];
        } elseif (is_string($config['client'])) {
            $client = Arr::get($this->config, 'clients.'.$config['client']);

            if ($client == null) {
                throw new InvalidArgumentException('Client '.$config['client'].' doesn\'t configured.');
            }

            $config['client'] = $client;
        }

        return $config;
    }

    /**
     * Resolve and register all configured services.
     */
    protected function resolveServices()
    {
        $services = Arr::get($this->config, 'services');

        if (!is_array($services) || empty($services)) {
            return;
        }

        foreach ($services as $config) {
            $this->resolveReference($config);
        }
    }

    /**
     * Register a service in the Homer.
     *
     * @param array $config
     *
     * @return mixed
     */
    public function resolveService(array $config)
    {
        return (new ServiceComponent($this->app, $config))
            ->setContext($this->context)
            ->setDispatcher($this->dispatcher)
            ->export();
    }

    /**
     * Get the context of current invoking.
     *
     * @return \Lawoole\Homer\Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Pass methods onto the calling context.
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->getContext()->$method(...$arguments);
    }
}
