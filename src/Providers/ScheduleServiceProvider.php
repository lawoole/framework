<?php
namespace Lawoole\Providers;

use Illuminate\Console\Application as ConsoleApplication;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * 注册服务提供者
     */
    public function register()
    {
        $this->app->singleton('schedule', function () {
            return new Schedule;
        });
    }

    /**
     * 启动服务提供者
     */
    public function boot()
    {
        if (ConsoleApplication::artisanBinary() == "'server'") {
            // 作为服务时不注册定时规则
            return;
        }

        $this->defineConsoleSchedule();
    }

    /**
     * 定义定时任务
     */
    protected function defineConsoleSchedule()
    {
        if (($schedules = $this->app['config']['console.schedules']) == null) {
            return;
        }

        $schedule = $this->app->make(Schedule::class);

        foreach ($schedules as $definition) {
            $type = Arr::get($definition, 'type', 'command');

            if (!method_exists($this, $method = 'define'.ucfirst($type).'Schedule')) {
                throw new InvalidArgumentException("Schedule [{$type}] is not support");
            }

            $this->$method($schedule, $definition);
        }
    }

    /**
     * 定义定时命令
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @param array $definition
     */
    protected function defineCommandSchedule(Schedule $schedule, array $definition)
    {
        $arguments = Arr::get($definition, 'arguments', []);

        $event = $schedule->command($definition['command'], $arguments);

        $this->configureEvent($event, $definition);
    }

    /**
     * 定义定时回调
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @param array $definition
     */
    protected function defineCallbackSchedule(Schedule $schedule, array $definition)
    {
        $arguments = Arr::get($definition, 'arguments', []);

        $event = $schedule->call($definition['callback'], $arguments);

        $this->configureEvent($event, $definition);
    }

    /**
     * 定义定时脚本
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @param array $definition
     */
    protected function defineScriptSchedule(Schedule $schedule, array $definition)
    {
        $arguments = Arr::get($definition, 'arguments', []);

        $event = $schedule->exec($definition['script'], $arguments);

        $this->configureEvent($event, $definition);
    }

    /**
     * 定义定时任务
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @param array $definition
     */
    protected function defineJobSchedule(Schedule $schedule, array $definition)
    {
        $queue = Arr::get($definition, 'queue', []);

        $event = $schedule->job($definition['job'], $queue);

        $this->configureEvent($event, $definition);
    }

    /**
     * 配置定时任务事件
     *
     * @param \Illuminate\Console\Scheduling\Event $event
     * @param array $definition
     */
    protected function configureEvent($event, array $definition)
    {
        if ($cron = Arr::get($definition, 'cron')) {
            $event->cron($cron);
        }
    }

    /**
     * 获得提供的服务名
     *
     * @return array
     */
    public function provides()
    {
        return ['schedule'];
    }
}
