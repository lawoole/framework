<?php
namespace Lawoole\Foundation\Console;

use Closure;
use Exception;
use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Events\Dispatcher;
use Lawoole\Contracts\Foundation\ApplicationInterface;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class Kernel implements KernelContract
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\ApplicationInterface
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
     * 判断命令是否已经注册
     *
     * @var bool
     */
    protected $commandsRegistered = false;

    /**
     * 初始化过程集合
     *
     * @var array
     */
    protected $bootstrappers = [
        \Lawoole\Foundation\Bootstrap\LoadConfigurations::class,
        \Lawoole\Foundation\Bootstrap\RegisterExceptionHandlers::class,
        \Lawoole\Foundation\Bootstrap\RegisterFacades::class,
        \Lawoole\Foundation\Bootstrap\RegisterServiceProviders::class,
        \Lawoole\Foundation\Bootstrap\BootProviders::class,
    ];

    /**
     * 创建 Console 处理核心
     *
     * @param \Lawoole\Contracts\Foundation\ApplicationInterface $app
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function __construct(ApplicationInterface $app, Dispatcher $events)
    {
        $this->app = $app;
        $this->events = $events;

        $this->app->booted(function () {
            $this->defineConsoleSchedule();
        });
    }

    /**
     * 初始化处理核心
     */
    public function bootstrap()
    {
        $this->app->bootstrapWith($this->bootstrappers);

        if (!$this->commandsRegistered) {
            // 注册自定义命令
            $this->commands();

            $this->commandsRegistered = true;
        }
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

            return $this->getArtisan()->run($input, $output);
        } catch (Exception $e) {
            // 处理异常
            $this->handleException($e, $output);

            return 1;
        } catch (Throwable $e) {
            // 转换为异常
            $e = new FatalThrowableError($e);

            // 处理异常
            $this->handleException($e, $output);

            return 1;
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
        throw new RuntimeException('Queueing commands is not supported.');
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
     * 定义定时任务
     */
    protected function defineConsoleSchedule()
    {
        $this->app->singleton(Schedule::class, function () {
            return new Schedule;
        });

        $schedule = $this->app->make(Schedule::class);

        $this->schedule($schedule);
    }

    /**
     * 注册定时任务
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        if (($schedules = $this->app['config']['console.schedules']) == null) {
            return;
        }

        foreach ($schedules as $definition) {
            if ($definition instanceof Closure) {
                // 如果是个闭包，直接执行
                call_user_func($definition, $schedule);

                continue;
            }

            if (file_exists($definition)) {
                tap($schedule, function ($schedule) use ($definition) {
                    include $definition;
                });
            }
        }
    }

    /**
     * 注册控制台命令
     */
    protected function commands()
    {
        // 注册自定义命令
        $commands = $this->app->make('config')->get('console.commands', []);

        $this->getArtisan()->resolveCommands($commands);
    }

    /**
     * 处理异常
     *
     * @param \Exception $e
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \Exception
     */
    protected function handleException(Exception $e, OutputInterface $output)
    {
        try {
            $handler = $this->app->make(ExceptionHandler::class);
        } catch (Exception $ex) {
            throw $e;
        }

        $handler->report($e);

        $handler->renderForConsole($output, $e);
    }
}
