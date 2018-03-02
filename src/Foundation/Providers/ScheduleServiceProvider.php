<?php
namespace Lawoole\Foundation\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register()
    {
        $this->app->singleton('schedule', function () {
            return new Schedule;
        });
    }

    /**
     * 启动服务
     */
    public function boot()
    {
        $this->loadSchedule($this->app['schedule']);
    }

    /**
     * 载入定时任务定义
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function loadSchedule($schedule)
    {
        if (($schedules = $this->app['config']['console.schedules']) == null) {
            return;
        }

        foreach ($schedules as $filePath) {
            tap($schedule, function ($schedule) use ($filePath) {
                include $filePath;
            });
        }
    }
}
