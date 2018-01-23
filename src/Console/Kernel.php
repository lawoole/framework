<?php
namespace Lawoole\Console;

use Exception;
use Illuminate\Console\Application as Artisan;
use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Lawoole\Application;
use RuntimeException;
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
        \Lawoole\Bootstrap\LoadConfigurations::class,
        \Lawoole\Bootstrap\RegisterExceptionHandlers::class,
        \Lawoole\Bootstrap\RegisterFacades::class,
        \Lawoole\Bootstrap\RegisterServiceProviders::class,
        \Lawoole\Bootstrap\BootProviders::class,
    ];

    /**
     * 内置命令集合
     *
     * @var array
     */
    protected $internalCommands = [
        'command.up',
        'command.down',
        'command.app.name',
        'command.cache.clear',
        'command.cache.forget',
        'command.cache.table',
        'command.factory.make',
        'command.migrate',
        'command.migrate.fresh',
        'command.migrate.install',
        'command.migrate.make',
        'command.migrate.refresh',
        'command.migrate.reset',
        'command.migrate.rollback',
        'command.migrate.status',
        'command.schedule.run',
        'command.schedule.finish',
        'command.seeder.make',
        'command.seed',
        'command.view.clear',
    ];

    /**
     * 创建 Console 处理核心
     *
     * @param \Lawoole\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
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
            $this->artisan = new Artisan($this->app, $this->app['events'], $this->app->version());
            $this->artisan->setName($this->app->name());

            // 注册命令
            $this->artisan->resolveCommands($this->getUsableCommands());
        }

        return $this->artisan;
    }

    /**
     * 获得可用的命令
     *
     * @return array
     */
    protected function getUsableCommands()
    {
        return array_merge(
            $this->internalCommands,
            $this->app->make('config')->get('console.commands', [])
        );
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
