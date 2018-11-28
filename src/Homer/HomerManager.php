<?php
namespace Lawoole\Homer;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Lawoole\Contracts\Homer\Homer as HomerContract;
use Lawoole\Contracts\Homer\Registrar;
use Lawoole\Homer\Calling\ProxyFactory;
use Lawoole\Homer\Components\ReferenceComponent;
use Lawoole\Homer\Components\ServiceComponent;
use Lawoole\Homer\Serialize\Factory as SerializerFactory;
use Lawoole\Homer\Transport\ClientFactory;
use Lawoole\Server\Events\ServerLaunching;

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
     * @var \Lawoole\Homer\Calling\Dispatcher
     */
    protected $dispatcher;

    /**
     * The proxy factory instance.
     *
     * @var \Lawoole\Homer\Calling\ProxyFactory
     */
    protected $proxyFactory;

    /**
     * The serializer factory instance.
     *
     * @var \Lawoole\Homer\Serialize\Factory
     */
    protected $serializerFactory;

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
     * @param \Lawoole\Homer\Context $context
     * @param \Lawoole\Homer\Calling\Dispatcher $dispatcher
     * @param array $config
     */
    public function __construct($app, $context, $dispatcher, array $config = [])
    {
        $this->app = $app;
        $this->context = $context;
        $this->dispatcher = $dispatcher;
        $this->config = $config;

        $this->proxyFactory = new ProxyFactory;
        $this->serializerFactory = new SerializerFactory;
        $this->clientFactory = new ClientFactory($app, $this->serializerFactory);
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
     * Gather the middleware for the invoker with resolved class names.
     *
     * @param array $middleware
     *
     * @return array
     */
    protected function gatherInvokerMiddleware($middleware)
    {
        $nameMiddleware = $this->config['name_middleware'] ?? [];
        $middlewareGroups = $this->config['middleware_groups'] ?? [];

        return Collection::make($middleware)->map(function ($middleware) use ($nameMiddleware, $middlewareGroups) {
            return (array) MiddlewareNameResolver::resolve($middleware, $nameMiddleware, $middlewareGroups);
        })->flatten()->all();
    }

    /**
     * Resolve and register all configured services.
     */
    protected function resolveServices()
    {
        // Do real export services when the server is launching.
        $this->app['events']->listen(ServerLaunching::class, function () {
            $services = Arr::get($this->config, 'services');

            if (! is_array($services) || empty($services)) {
                return;
            }

            foreach ($services as $config) {
                $this->resolveService($config);
            }
        });
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
        $middleware = $this->gatherInvokerMiddleware(array_merge(
            $this->config['middleware'] ?? [],
            $config['middleware'] ?? []
        ));

        return (new ServiceComponent($this->app, $config))
            ->setContext($this->context)
            ->setDispatcher($this->dispatcher)
            ->setMiddleware($middleware)
            ->export();
    }

    /**
     * Resolve and register all configured references.
     */
    protected function resolveReferences()
    {
        $references = $this->config['references'] ?? [];

        if (! is_array($references) || empty($references)) {
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

        $middleware = $this->gatherInvokerMiddleware(array_merge(
            $config['client']['middleware'] ?? [],
            $config['middleware'] ?? []
        ));

        return (new ReferenceComponent($this->app, $config))
            ->setContext($this->context)
            ->setProxyFactory($this->proxyFactory)
            ->setClientFactory($this->clientFactory)
            ->setMiddleware($middleware)
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
        if (! isset($config['client'])) {
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
