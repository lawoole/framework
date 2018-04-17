<?php
namespace Lawoole\Homer;

use BadMethodCallException;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Lawoole\Contracts\Foundation\Application;
use Lawoole\Homer\Invokers\ConcreteInvoker;
use Lawoole\Homer\Invokers\RemoteInvoker;
use Lawoole\Homer\Transport\Client;
use Lawoole\Homer\Transport\Http\HttpClient;
use Lawoole\Homer\Transport\Whisper\WhisperClient;

class HomerManager
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * 配置
     *
     * @var array
     */
    protected $config;

    /**
     * 是否已经启动
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * 调用上下文
     *
     * @var \Lawoole\Homer\Context
     */
    protected $context;

    /**
     * 调用调度器
     *
     * @var \Lawoole\Homer\Dispatcher
     */
    protected $dispatcher;

    /**
     * 客户端对象
     *
     * @var \Lawoole\Homer\Transport\Client[]
     */
    protected $clients = [];

    /**
     * 创建 Homer 管理对象
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     * @param array $config
     */
    public function __construct(Application $app, array $config = [])
    {
        $this->app = $app;
        $this->context = $app['homer.context'];
        $this->dispatcher = $app['homer.dispatcher'];
        $this->config = $config;
    }

    /**
     * 启动 Homer
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
     * 解析并注册引用
     */
    protected function resolveReferences()
    {
        $references = $this->config['references'] ?? [];

        if (!is_array($references) || empty($references)) {
            return;
        }

        foreach ($references as $reference) {
            $this->resolveReference($reference);
        }
    }

    /**
     * 解析并注册引用
     *
     * @param array $config
     */
    protected function resolveReference(array $config)
    {
        $interface = $config['interface'];

        if (isset($config['client'])) {
            $client = $this->getClient($this->config['clients.'.$config['client']]);
        } else {
            $client = $this->getClient(['url' => $config['url']]);
        }

        $invoker = $this->createRemoteInvoker($client, $config['interface'], $config['options'] ?? []);

        $proxy = new Proxy($invoker);

        $identify = $config['id'] ?? $interface;

        $this->app->instance($identify, $proxy);
    }

    /**
     * 创建远程调用器
     *
     * @param \Lawoole\Homer\Transport\Client $client
     * @param $interface
     * @param array $options
     *
     * @return \Lawoole\Homer\Invokers\RemoteInvoker
     */
    protected function createRemoteInvoker(Client $client, $interface, array $options = [])
    {
        return new RemoteInvoker($this->context, $client, $interface, $options);
    }

    /**
     * 获得客户端
     *
     * @param array $config
     *
     * @return \Lawoole\Homer\Transport\Client
     */
    protected function getClient(array $config = [])
    {
        $url = Arr::pull($config, 'url');

        $clientKey = $this->getClientKey($url, $config);

        if (isset($this->clients[$clientKey])) {
            return $this->clients[$clientKey];
        }

        return $this->clients[$clientKey] = $this->resolveClient($url, $config);
    }

    /**
     * 获得客户端标识
     *
     * @param string $url
     * @param array $options
     *
     * @return string
     */
    protected function getClientKey($url, array $options = [])
    {
        ksort($options);

        return $url.'?'.http_build_query($options);
    }

    /**
     * 创建客户端
     *
     * @param string $url
     * @param array $options
     *
     * @return \Lawoole\Homer\Transport\Client
     */
    protected function resolveClient($url, array $options = [])
    {
        $urls = parse_url($url);

        switch ($urls['scheme']) {
            case 'homer':
                return new HttpClient($urls['host'], $urls['port'], $options);
            case 'whisper':
                return new WhisperClient($urls['host'], $urls['port'], $options);
            default:
                throw new InvalidArgumentException('Protocol '.$urls['scheme'].' is not supported for Homer.');
        }
    }

    /**
     * 解析并注册服务
     */
    protected function resolveServices()
    {
        $services = Arr::get($this->config, 'services');

        if (!is_array($services) || empty($services)) {
            return;
        }

        foreach ($services as $service) {
            $this->resolveService($service);
        }
    }

    /**
     * 解析并注册服务
     *
     * @param array $config
     */
    protected function resolveService(array $config)
    {
        $interface = Arr::pull($config, 'interface');
        $concrete = Arr::pull($config, 'refer') ?? $interface;

        $invoker = new ConcreteInvoker($this->app, $interface, $concrete, $config);

        $this->dispatcher->exportInvoker($invoker);
    }

    /**
     * 创建实际调用器
     *
     * @param string $interface
     * @param string|object|\Closure $concrete
     * @param array $options
     *
     * @return \Lawoole\Homer\Invokers\ConcreteInvoker
     */
    protected function createConcreteInvoker($interface, $concrete, array $options = [])
    {
        return new ConcreteInvoker($this->app, $interface, $concrete, $options);
    }

    /**
     * 获得调用上下文
     *
     * @return \Lawoole\Homer\Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * 动态调用代理
     *
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $context = $this->getContext();

        if (method_exists($context, $method)) {
            return call_user_func_array([$context, $method], $arguments);
        }

        throw new BadMethodCallException(sprintf('Method %s::%s does not exist.', static::class, $method));
    }
}
