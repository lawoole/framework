<?php
namespace Lawoole\Providers;

use Closure;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * 注册服务提供�
     */
    public function register()
    {
        $this->app->alias('schedule', Schedule::class);

        $this->app->singleton('schedule', function () {
            $schedule = new Schedule;

            $this->loadSchedules($schedule);

            return $schedule;
        });
    }

    /**
     * 载入定时任务规则
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function loadSchedules($schedule)
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
     * 获得提供的服务名
     *
     * @return array
     */
    public function provides()
    {
        return ['schedule'];
    }
}
