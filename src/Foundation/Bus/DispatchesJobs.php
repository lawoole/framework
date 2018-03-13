<?php
namespace Lawoole\Foundation\Bus;

use Illuminate\Contracts\Bus\Dispatcher;

trait DispatchesJobs
{
    /**
     * 分发任务
     *
     * @param mixed $job
     *
     * @return mixed
     */
    protected function dispatch($job)
    {
        return app(Dispatcher::class)->dispatch($job);
    }

    /**
     * 分发任务并立即执行
     *
     * @param mixed $job
     *
     * @return mixed
     */
    public function dispatchNow($job)
    {
        return app(Dispatcher::class)->dispatchNow($job);
    }
}
