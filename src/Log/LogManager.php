<?php
namespace Lawoole\Log;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class LogManager
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\ApplicationInterface
     */
    protected $app;

    /**
     * 日志配置
     *
     * @var array
     */
    protected $config;

    /**
     * 事件调度器
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $dispatcher;

    /**
     * 日志写入器
     *
     * @var array
     */
    protected $writers = [];

    /**
     * 创建日志管理器
     *
     * @param \Lawoole\Contracts\Foundation\ApplicationInterface $app
     * @param array $config
     * @param \Illuminate\Contracts\Events\Dispatcher $dispatcher
     */
    public function __construct($app, array $config, Dispatcher $dispatcher = null)
    {
        $this->app = $app;
        $this->config = $config;
        $this->dispatcher = $dispatcher;
    }

    /**
     * 获得日志写入器
     *
     * @param string|null $name
     *
     * @return \Illuminate\Log\Writer
     */
    public function writer($name = null)
    {
        $name = $name ?: $this->getDefaultWriter();

        if (isset($this->writers[$name])) {
            return $this->writers[$name];
        }

        return $this->writers[$name] = $this->createLogger($name);
    }

    /**
     * 获得默认写入器名
     *
     * @return string
     */
    public function getDefaultWriter()
    {
        return $this->config['default'];
    }

    /**
     * 创建日志写入器
     *
     * @param string $name
     * @return \Illuminate\Log\Writer
     *
     * @throws \InvalidArgumentException
     */
    protected function createLogger($name)
    {
        if (isset($this->config['writers'][$name])) {
            return $this->factory($name, $this->config['writers'][$name])->getWriter();
        }

        throw new InvalidArgumentException("Log writer [{$name}] not configured");
    }

    /**
     * 获得日志写入器创建工厂
     *
     * @param string $name
     * @param array $config
     *
     * @return \Lawoole\Log\WriterFactory
     */
    protected function factory($name, array $config)
    {
        $config = array_merge(
            Arr::get($this->config, 'options', []), $config
        );

        return new WriterFactory($this->app, $name, $config, $this->dispatcher);
    }

    /**
     * 动态代理日志调用
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->writer()->{$method}(...$parameters);
    }
}
