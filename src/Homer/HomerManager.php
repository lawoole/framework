<?php
namespace Lawoole\Homer;

use BadMethodCallException;
use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Lawoole\Homer\Rpc\Proxy;
use Lawoole\Homer\Transport\Http\HttpClient;
use Lawoole\Homer\Transport\Whisper\WhisperClient;

class HomerManager
{
    /**
     * 容器
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

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
     * 创建 Homer 管理对象
     *
     * @param \Illuminate\Container\Container $container
     * @param array $config
     */
    public function __construct(Container $container, array $config = [])
    {
        $this->container = $container;
        $this->config = $config;

        $this->context = new Context;
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
        $components = parse_url($config['url']);


        $invoker = $protocol->refer($interface, $url);

        $proxy = new Proxy($invoker);

        $identify = $config['id'] ?? $interface;

        $this->container->instance($identify, $proxy);
    }

    /**
     * 创建客户端
     *
     * @param array $config
     *
     * @return \Lawoole\Homer\Transport\Client
     */
    protected function resolveClient(array $config)
    {
        $urls = parse_url(Arr::pull($config, 'url'));

        switch ($urls['scheme']) {
            case 'http':
                return new HttpClient($urls['host'], $urls['port'], $config);
            case 'whisper':
                return new WhisperClient($urls['host'], $urls['port'], $config);
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
        $interface = $config['interface'];
        $url = Url::valueOf($config['url']);

        $refer = $config['refer'] ?? $interface;

        $invoker = new ConcreteInvoker($this->app, $interface, $refer, $url);

        $protocol = ExtensionLoader::getExtensionLoader(ProtocolInterface::class)->get($url->getScheme());

        $protocol->export($invoker);
    }

    /**
     * 获得调用上下文
     *
     * @return \Lawoole\Homer\Rpc\Context
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
