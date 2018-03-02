<?php
namespace Lawoole\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Console\Scheduling\CallbackEvent call($callback, array $parameters = [])
 * @method static \Illuminate\Console\Scheduling\Event command($command, array $parameters = [])
 * @method static \Illuminate\Console\Scheduling\CallbackEvent job($job, $queue = null)
 * @method static \Illuminate\Console\Scheduling\Event exec($command, array $parameters = [])
 *
 * @see \Illuminate\Console\Scheduling\Schedule
 */
class Schedule extends Facade
{
    /**
     * 获得服务名
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'schedule';
    }
}
