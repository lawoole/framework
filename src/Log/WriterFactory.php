<?php
namespace Lawoole\Log;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Log\Writer;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Monolog\Logger as Monolog;

class WriterFactory
{
    /**
     * 服务容器
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * 日志写入器名
     *
     * @var string
     */
    protected $name;

    /**
     * 日志写入器配置
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
     * 创建日志写入器工厂对象实例
     *
     * @param \Lawoole\Contracts\Foundation\ApplicationInterface $app
     * @param string $name
     * @param array $config
     * @param \Illuminate\Contracts\Events\Dispatcher|null $dispatcher
     */
    public function __construct($app, $name, array $config, Dispatcher $dispatcher = null)
    {
        $this->app = $app;
        $this->name = $name;
        $this->config = $config;
        $this->dispatcher = $dispatcher;
    }

    /**
     * 获得日志写入器
     *
     * @return \Illuminate\Log\Writer
     */
    public function getWriter()
    {
        $monolog = new Monolog($this->channel());

        $writer = new Writer($monolog, $this->dispatcher);

        $this->configureHandler($writer);

        return $writer;
    }

    /**
     * 配置日志处理器
     *
     * @param \Illuminate\Log\Writer $writer
     */
    protected function configureHandler(Writer $writer)
    {
        $this->{'configure'.Str::studly($this->handler()).'Handler'}($writer);
    }

    /**
     * 配置简单日志处理器
     *
     * @param \Illuminate\Log\Writer $log
     */
    protected function configureSingleHandler(Writer $log)
    {
        $log->useFiles($this->logFilePath(), $this->logLevel());
    }

    /**
     * 配置日历日志处理器
     *
     * @param \Illuminate\Log\Writer $log
     */
    protected function configureDailyHandler(Writer $log)
    {
        $log->useDailyFiles($this->logFilePath(), $this->maxFiles(), $this->logLevel());
    }

    /**
     * 配置系统日志处理器
     *
     * @param \Illuminate\Log\Writer $log
     */
    protected function configureSystemHandler(Writer $log)
    {
        $log->useSyslog($this->logName(), $this->logLevel());
    }

    /**
     * 配置标准错误日志处理器
     *
     * @param \Illuminate\Log\Writer $log
     */
    protected function configureErrorHandler(Writer $log)
    {
        $log->useErrorLog($this->logLevel());
    }

    /**
     * 获得日志频道
     *
     * @return string
     */
    protected function channel()
    {
        if (isset($this->config['channel']) && $channel = $this->config['channel']) {
            return $channel;
        } elseif ($channel = $this->app->environment()) {
            return $channel;
        }

        return 'product';
    }

    /**
     * 获得日志处理器名
     *
     * @return string
     */
    protected function handler()
    {
        return Arr::get($this->config, 'driver', 'single');
    }

    /**
     * 获得日志名
     *
     * @return string
     */
    protected function logName()
    {
        return Arr::get($this->config, 'name', $this->name);
    }

    /**
     * 获得日志路径
     *
     * @return string
     */
    protected function logFilePath()
    {
        if (isset($this->config['file']) && $path = $this->config['file']) {
            return $path;
        }

        $name = $this->logName();

        return $this->app->storagePath("logs/{$name}.log");
    }

    /**
     * 获得日志
     *
     * @return string
     */
    protected function logLevel()
    {
        return Arr::get($this->config, 'level', 'debug');
    }

    /**
     * 获得日志文件最大数量
     *
     * @return int
     */
    protected function maxFiles()
    {
        return Arr::get($this->config, 'max_files', 0);
    }
}
