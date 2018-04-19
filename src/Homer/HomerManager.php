<?php
namespace Lawoole\Homer;

use BadMethodCallException;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Lawoole\Contracts\Foundation\Application;
use Lawoole\Homer\Components\ReferenceComponent;
use Lawoole\Homer\Components\ServiceComponent;
use Lawoole\Homer\Transport\ClientFactory;

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
     * 客户端工厂
     *
     * @var \Lawoole\Homer\Transport\ClientFactory
     */
    protected $clientFactory;

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

        $this->clientFactory = new ClientFactory;
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

        foreach ($references as $config) {
            if (!isset($config['client'])) {
                $config['client'] = [
                    'url' => $config['url']
                ];
            } elseif (is_string($config['client'])) {
                $client = Arr::get($this->config, 'clients.'.$config['client']);

                if ($client == null) {
                    throw new InvalidArgumentException('Client '.$config['client'].' doesn\'t configured.');
                }

                $config['client'] = $client;
            }

            (new ReferenceComponent($this->app, $config))
                ->setContext($this->context)
                ->setClientFactory($this->clientFactory)
                ->refer();
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

        foreach ($services as $config) {
            (new ServiceComponent($this->app, $config))
                ->setContext($this->context)
                ->setDispatcher($this->dispatcher)
                ->export();
        }
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
