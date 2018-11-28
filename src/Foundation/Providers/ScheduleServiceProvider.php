<?php
namespace Lawoole\Foundation\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('schedule', function () {
            return new Schedule;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->loadSchedule($this->app['schedule']);
    }

    /**
     * Load the schedule tasks.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function loadSchedule($schedule)
    {
        if (($schedules = $this->app['config']['console.schedules']) == null) {
            return;
        }

        foreach ($schedules as $definition) {
            $type = $definition['type'] ?? 'command';

            if (! method_exists($this, $method = 'define'.ucfirst($type).'Schedule')) {
                throw new InvalidArgumentException("Schedule [{$type}] is not support.");
            }

            $this->$method($schedule, $definition);
        }
    }

    /**
     * Define schedule command.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @param array $definition
     */
    protected function defineCommandSchedule(Schedule $schedule, array $definition)
    {
        $arguments = $definition['arguments'] ?? [];

        $event = $schedule->command($definition['command'], $arguments);

        $this->configureEvent($event, $definition);
    }

    /**
     * Define schedule callback.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @param array $definition
     */
    protected function defineCallbackSchedule(Schedule $schedule, array $definition)
    {
        $arguments = $definition['arguments'] ?? [];

        $event = $schedule->call($definition['callback'], $arguments);

        $this->configureEvent($event, $definition);
    }

    /**
     * Define schedule script.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @param array $definition
     */
    protected function defineScriptSchedule(Schedule $schedule, array $definition)
    {
        $arguments = $definition['arguments'] ?? [];

        $event = $schedule->exec($definition['script'], $arguments);

        $this->configureEvent($event, $definition);
    }

    /**
     * Define schedule job.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @param array $definition
     */
    protected function defineJobSchedule(Schedule $schedule, array $definition)
    {
        $queue = $definition['queue'] ?? [];

        $event = $schedule->job($definition['job'], $queue);

        $this->configureEvent($event, $definition);
    }

    /**
     * Configure the schedule event.
     *
     * @param \Illuminate\Console\Scheduling\Event $event
     * @param array $definition
     */
    protected function configureEvent($event, array $definition)
    {
        if (isset($definition['cron'])) {
            $event->cron($definition['cron']);
        }

        if (isset($definition['singleton']) && $definition['singleton']) {
            $event->onOneServer();
        }

        if (isset($definition['mutex']) && $definition['mutex']) {
            $event->withoutOverlapping();
        }
    }
}
