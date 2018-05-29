<?php
namespace Lawoole\Server\Process;

class ProcessHandler
{
    /**
     * 在进程绑定到服务时调用
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\Process\Process $process
     */
    public function onBind($server, $process)
    {
    }

    /**
     * 在进程启动后调用
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\Process\Process $process
     */
    public function onStart($server, $process)
    {
    }

    /**
     * 收到父进程消息时调用
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\Process\Process $process
     * @param string $data
     */
    public function onMessage($server, $process, $data)
    {
    }
}
