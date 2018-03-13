<?php
namespace Lawoole\Foundation\Bus;

use Illuminate\Contracts\Bus\Dispatcher;

class PendingDispatch
{
    /**
     * 任务
     *
     * @var mixed
     */
    protected $job;

    /**
     * 创建任务推送对象
     *
     * @param mixed $job
     */
    public function __construct($job)
    {
        $this->job = $job;
    }

    /**
     * 设置队列连接
     *
     * @param string|null $connection
     *
     * @return $this
     */
    public function onConnection($connection)
    {
        $this->job->onConnection($connection);

        return $this;
    }

    /**
     * 设置处理队列
     *
     * @param string|null $queue
     *
     * @return $this
     */
    public function onQueue($queue)
    {
        $this->job->onQueue($queue);

        return $this;
    }

    /**
     * 设置整个任务链的队列连接
     *
     * @param string|null $connection
     *
     * @return $this
     */
    public function allOnConnection($connection)
    {
        $this->job->allOnConnection($connection);

        return $this;
    }

    /**
     * 设置整个任务链的处理队列
     *
     * @param string|null $queue
     *
     * @return $this
     */
    public function allOnQueue($queue)
    {
        $this->job->allOnQueue($queue);

        return $this;
    }

    /**
     * 设置任务处理延迟
     *
     * @param \DateTime|int|null $delay
     *
     * @return $this
     */
    public function delay($delay)
    {
        $this->job->delay($delay);

        return $this;
    }

    /**
     * 设置任务链中的后继任务
     *
     * @param array $chain
     *
     * @return $this
     */
    public function chain($chain)
    {
        $this->job->chain($chain);

        return $this;
    }

    /**
     * 析构处理
     */
    public function __destruct()
    {
        app(Dispatcher::class)->dispatch($this->job);
    }
}
