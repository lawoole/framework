<?php
namespace Lawoole\Contracts\Server;

interface Process
{
    /**
     * 获得 Swoole 进程对象
     *
     * @return \Swoole\Process
     */
    public function getSwooleProcess();

    /**
     * 退出进程
     *
     * @param int $status
     */
    public function quit($status = 0);
}
