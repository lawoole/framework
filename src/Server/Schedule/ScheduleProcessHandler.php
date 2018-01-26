<?php
namespace Lawoole\Server\Schedule;

use Illuminate\Console\Scheduling\Schedule;
use Lawoole\Contracts\Foundation\ApplicationInterface;
use Lawoole\Swoole\Handlers\ProcessHandlerInterface;
use Swoole\Timer;

class ScheduleProcessHandler implements ProcessHandlerInterface
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\ApplicationInterface
     */
    protected $app;

    /**
     * 定时任务定义对象
     *
     * @var \Illuminate\Console\Scheduling\Schedule
     */
    protected $schedule;

    /**
     * 创建定时任务处理器
     *
     * @param \Lawoole\Contracts\Foundation\ApplicationInterface $app
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    public function __construct(ApplicationInterface $app, Schedule $schedule)
    {
        $this->app = $app;
        $this->schedule = $schedule;
    }

    /**
     * 当进程启动时调用
     *
     * @param \Lawoole\Swoole\Process $process
     */
    public function onStart($process)
    {
        $name = $this->app->name();

        // 设置进程名
        swoole_set_process_name("{$name} : Schedule");

        // 定时器
        Timer::tick(1000, function () {
            $this->schedule();
        });
    }

    /**
     * 当收到管道数据时调用
     *
     * @param \Lawoole\Swoole\Process $process
     * @param string $data
     */
    public function onReceive($process, $data)
    {
    }

    /**
     * 执行定时器
     */
    protected function schedule() {
        foreach ($this->schedule->dueEvents($this->app) as $event) {
            if (!$event->filtersPass($this->app)) {
                continue;
            }

            $event->run($this->app);
        }
    }
}
