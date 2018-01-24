<?php
namespace Lawoole\Swoole\Handlers;

interface ServerHandler
{
    /**
     * 在主进程启动时调用
     *
     * @param \Lawoole\Swoole\Server $server
     */
    public function onStart($server);

    /**
     * 在主进程结束时调用
     *
     * @param \Lawoole\Swoole\Server $server
     */
    public function onShutdown($server);

    /**
     * 管理进程启动时调用
     *
     * @param \Lawoole\Swoole\Server $server
     */
    public function onManagerStart($server);

    /**
     * 管理进程结束时调用
     *
     * @param \Lawoole\Swoole\Server $server
     */
    public function onManagerStop($server);

    /**
     * 在工作进程启动时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param int $workerId
     */
    public function onWorkerStart($server, $workerId);

    /**
     * 在工作进程结束时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param int $workerId
     */
    public function onWorkerStop($server, $workerId);

    /**
     * 在工作进程平缓退出的每次事件循环结束时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param int $workerId
     */
    public function onWorkerExit($server, $workerId);

    /**
     * 工作进程异常退出时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param int $workerId
     * @param int $workerPid
     * @param int $exitCode
     * @param int $signal
     */
    public function onWorkerError($server, $workerId, $workerPid, $exitCode, $signal);

    /**
     * 任务工作进程收到任务时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param int $taskId
     * @param int $srcWorkerId
     * @param mixed $data
     */
    public function onTask($server, $taskId, $srcWorkerId, $data);

    /**
     * 任务工作结束，通知到工作进程时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param int $taskId
     * @param mixed $data
     */
    public function onFinish($server, $taskId, $data);

    /**
     * 接收到进程间管道消息时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param int $srcWorkerId
     * @param mixed $data
     */
    public function onPipeMessage($server, $srcWorkerId, $data);
}
