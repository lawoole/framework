<?php
namespace Lawoole\Swoole\Handlers;

interface ProcessHandlerInterface
{
    /**
     * 当进程启动时调用
     *
     * @param \Lawoole\Swoole\Process $process
     */
    public function onStart($process);

    /**
     * 当收到管道数据时调用
     *
     * @param \Lawoole\Swoole\Process $process
     * @param string $data
     */
    public function onReceive($process, $data);
}
