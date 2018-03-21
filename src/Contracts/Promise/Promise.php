<?php
namespace Lawoole\Contracts\Promise;

interface Promise
{
    /**
     * 注册接受与拒绝回调
     *
     * @param callable $onFulfilled
     * @param callable $onRejected
     *
     * @return \Lawoole\Contracts\Promise\Promise
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null);

    /**
     * 注册接受与拒绝回调
     *
     * @param callable $onFulfilled
     * @param callable $onRejected
     */
    public function done(callable $onFulfilled = null, callable $onRejected = null);

    /**
     * 注册拒绝回调
     *
     * @param callable $onRejected
     *
     * @return \Lawoole\Contracts\Promise\Promise
     */
    public function otherwise(callable $onRejected);

    /**
     * 注册终态回调
     *
     * @param callable $onFinally
     *
     * @return \Lawoole\Contracts\Promise\Promise
     */
    public function always(callable $onFinally);
}
