<?php
namespace Lawoole\Foundation\Console;

use Exception;
use Illuminate\Console\Application as Artisan;
use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Events\Dispatcher;
use Lawoole\Application;
use RuntimeException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class Kernel implements KernelContract
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Application
     */
    protected $app;

    /**
     * 事件分发器
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * 控制台应用实例
     *
     * @var \Illuminate\Console\Application
     */
    protected $artisan;

    /**
     * 初始化过程集合
     *
     * @var array
     */
    protected $bootstrappers = [
        \Lawoole\Foundation\Bootstrap\LoadConfigurations::class,
        \Lawoole\Foundation\Bootstrap\RegisterExceptionHandlers::class,
        \Lawoole\Foundation\Bootstrap\RegisterFacades::class,
        \Lawoole\Foundation\Bootstrap\SetRequestForConsole::class,
        \Lawoole\Foundation\Bootstrap\RegisterServiceProviders::class,
        \Lawoole\Foundation\Bootstrap\BootProviders::class,
    ];

    /**
     * 创建 Console 处理核心
     *
     * @param \Lawoole\Application $app
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function __construct(Application $app, Dispatcher $events)
    {
        $this->app = $app;
        $this->events = $events;
    }

    /**
     * 初始化处理核心
     */
    public function bootstrap()
    {
        $this->app->bootstrapWith($this->bootstrappers);
    }

    /**
     * 处理 Console 请求
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     *
     * @throws \Exception
     */
    public function handle($input, $output = null)
    {
        try {
            $this->bootstrap();

            $output = $output ?: new ConsoleOutput;

            $this->app->instance('console.input', $input);
            $this->app->instance('console.output', $output);

            return $this->getArtisan()->run($input, $output);
        } catch (Exception $e) {
            return $this->handleException($e, $output);
        } catch (Throwable $e) {
            return $this->handleException(new FatalThrowableError($e), $output);
        }
    }

    /**
     * 调用控制台命令
     *
     * @param string $command
     * @param array $parameters
     * @param \Symfony\Component\Console\Output\OutputInterface $outputBuffer
     *
     * @return int
     */
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        $this->bootstrap();

        return $this->getArtisan()->call($command, $parameters, $outputBuffer);
    }

    /**
     * 将命令加入执行队列
     *
     * @param string $command
     * @param array $parameters
     *
     * @return mixed
     */
    public function queue($command, array $parameters = [])
    {
        // 这个功能暂不支持
        throw new RuntimeException('Queueing commands is not supported');
    }

    /**
     * 获得所有已注册的命令
     *
     * @return array
     */
    public function all()
    {
        $this->bootstrap();

        return $this->getArtisan()->all();
    }

    /**
     * 获得最后执行命令的输出
     *
     * @return string
     */
    public function output()
    {
        $this->bootstrap();

        return $this->getArtisan()->output();
    }

    /**
     * 设置控制台应用实例
     *
     * @param \Illuminate\Console\Application $artisan
     */
    public function setArtisan($artisan)
    {
        $this->artisan = $artisan;
    }

    /**
     * 获得控制台应用实例
     *
     * @return \Illuminate\Console\Application
     */
    protected function getArtisan()
    {
        if ($this->artisan == null) {
            $this->artisan = new Artisan($this->app, $this->events, $this->app->version());
            $this->artisan->setName($this->app->name());
        }

        return $this->artisan;
    }

    /**
     * 终止请求处理
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param int $status
     */
    public function terminate($input, $status)
    {
        $this->app->terminate();
    }

    /**
     * 处理异常
     *
     * @param \Exception $e
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function handleException(Exception $e, OutputInterface $output)
    {
        $handler = $this->app->make(ExceptionHandler::class);

        $handler->report($e);

        $handler->renderForConsole($output, $e);

        return 1;
    }
}
