<?php
namespace Lawoole\Server;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Lawoole\Console\OutputStyle;
use Lawoole\Server\Responses\TaskReceivedResponse;
use Lawoole\Swoole\Handlers\ServerHandlerInterface;
use Lawoole\Swoole\Handlers\ServerSocketBufferHandlerInterface;
use Lawoole\Swoole\Handlers\TcpServerSocketHandlerInterface;
use Lawoole\Task\Message;
use Lawoole\Task\Task;
use Lawoole\Task\TaskResponse;
use RuntimeException;

class ServerHandler implements ServerHandlerInterface, ServerSocketBufferHandlerInterface,
    TcpServerSocketHandlerInterface
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\ApplicationInterface
     */
    protected $app;

    /**
     * 控制台格式输出
     *
     * @var \Lawoole\Console\OutputStyle
     */
    protected $outputStyle;

    /**
     * 创建服务事件处理器
     *
     * @param \Lawoole\Contracts\Foundation\ApplicationInterface $app
     * @param \Lawoole\Console\OutputStyle $outputStyle
     */
    public function __construct($app, OutputStyle $outputStyle)
    {
        $this->app = $app;
        $this->outputStyle = $outputStyle;
    }

    /**
     * 获得服务容器
     *
     * @return \Lawoole\Contracts\Foundation\ApplicationInterface
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * 在服务即将启动时调用
     *
     * @param \Lawoole\Swoole\Server $server
     */
    public function onLaunch($server)
    {
        $name = $this->app->name();

        $this->outputStyle->line("{$name} server is launching.");
    }

    /**
     * 在主进程启动时调用
     *
     * @param \Lawoole\Swoole\Server $server
     */
    public function onStart($server)
    {
        $name = $this->app->name();

        $this->outputStyle->info("{$name} server is running.");

        // 设置进程名
        swoole_set_process_name("{$name} : Master");

        // 共享至容器
        $this->app->instance('server', $server);
        $this->app->instance('server.swoole', $server->getSwooleServer());
        $this->app->instance('server.output', $this->outputStyle);
    }

    /**
     * 在主进程结束时调用
     *
     * @param \Lawoole\Swoole\Server $server
     */
    public function onShutdown($server)
    {
        // 移除容器共享
        $this->app->forgetInstance('server');
        $this->app->forgetInstance('server.swoole');
        $this->app->forgetInstance('server.output');
    }

    /**
     * 管理进程启动时调用
     *
     * @param \Lawoole\Swoole\Server $server
     */
    public function onManagerStart($server)
    {
        $name = $this->app->name();

        // 设置进程名
        swoole_set_process_name("{$name} : Manager");

        // 共享至容器
        $this->app->instance('server', $server);
        $this->app->instance('server.swoole', $server->getSwooleServer());
        $this->app->instance('server.output', $this->outputStyle);
    }

    /**
     * 管理进程结束时调用
     *
     * @param \Lawoole\Swoole\Server $server
     */
    public function onManagerStop($server)
    {
        // 移除容器共享
        $this->app->forgetInstance('server');
        $this->app->forgetInstance('server.swoole');
        $this->app->forgetInstance('server.output');
    }

    /**
     * 在工作进程启动时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param int $workerId
     */
    public function onWorkerStart($server, $workerId)
    {
        $name = $this->app->name();

        // 设置进程名
        swoole_set_process_name("{$name} : Worker {$workerId}");

        // 共享至容器
        $this->app->instance('server', $server);
        $this->app->instance('server.swoole', $server->getSwooleServer());
        $this->app->instance('server.output', $this->outputStyle);
        $this->app->instance('server.worker.id', $workerId);
        $this->app->instance('server.worker.task', $server->isTaskWorker());
    }

    /**
     * 在工作进程结束时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param int $workerId
     */
    public function onWorkerStop($server, $workerId)
    {
        // 移除容器共享
        $this->app->forgetInstance('server');
        $this->app->forgetInstance('server.swoole');
        $this->app->forgetInstance('server.output');
        $this->app->forgetInstance('server.worker.id');
        $this->app->forgetInstance('server.worker.task');
    }

    /**
     * 在工作进程平缓退出的每次事件循环结束时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param int $workerId
     */
    public function onWorkerExit($server, $workerId)
    {

    }

    /**
     * 工作进程异常退出时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param int $workerId
     * @param int $workerPid
     * @param int $exitCode
     * @param int $signal
     */
    public function onWorkerError($server, $workerId, $workerPid, $exitCode, $signal)
    {
        $message = "Worker {$workerId} exit with code {$exitCode}, signal {$signal}, pid {$workerPid}.";

        try {
            $this->reportException(new RuntimeException($message));
        } catch (Exception $e) {
            // Ignore
        }
    }

    /**
     * 任务工作进程收到任务时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param int $taskId
     * @param int $srcWorkerId
     * @param mixed $data
     */
    public function onTask($server, $taskId, $srcWorkerId, $data)
    {
        if ($data instanceof Task) {
            $data->setTaskId($taskId);
            $data->setSrcWorkerId($srcWorkerId);

            if (method_exists($data, 'handle')) {
                $response = $this->app->call([$data, 'handle']);

                // 如果 handle 方法返回了任务响应对象，则推送响应
                if ($response instanceof TaskResponse) {
                    $server->pushTaskResponse($response);
                }
                // 如果任务存在完成方法，则响应任务本身
                elseif (method_exists($data, 'finish')) {
                    $server->pushTaskResponse($data);
                }
            } else {
                // 默认：任务已接收响应
                $server->pushTaskResponse(new TaskReceivedResponse);
            }
        }
    }

    /**
     * 任务工作结束，通知到工作进程时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param int $taskId
     * @param mixed $data
     */
    public function onFinish($server, $taskId, $data)
    {
        // 任务执行中返回了任务响应，处理任务响应
        if ($data instanceof TaskResponse) {
            $data->setTaskId($taskId);
            $data->setSrcWorkerId($server->getWorkerId());

            if (method_exists($data, 'handle')) {
                $this->app->call([$data, 'handle']);
            }
        }
        // 任务执行中返回了任务本身，执行任务的完成方法
        elseif ($data instanceof Task) {
            $data->setTaskId($taskId);
            $data->setSrcWorkerId($server->getWorkerId());

            if (method_exists($data, 'finish')) {
                $this->app->call([$data, 'finish']);
            }
        }
    }

    /**
     * 接收到进程间管道消息时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param int $srcWorkerId
     * @param mixed $data
     */
    public function onPipeMessage($server, $srcWorkerId, $data)
    {
        if ($data instanceof Message) {
            // 设置消息信息
            $data->setSrcWorkerId($srcWorkerId);

            if (method_exists($data, 'handle')) {
                $this->app->call([$data, 'handle']);
            }
        }
    }

    /**
     * 当缓存区达到高位线时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param int $fd
     */
    public function onBufferFull($server, $serverSocket, $fd)
    {
        $message = "The buffer for [{$fd}] reached the high watermark.";

        Log::warning($message);

        $this->outputStyle->warn($message);
    }

    /**
     * 当缓存区降至低位线时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param int $fd
     */
    public function onBufferEmpty($server, $serverSocket, $fd)
    {
        $message = "The buffer for [{$fd}] reached the low watermark.";

        Log::info($message);

        $this->outputStyle->info($message);
    }

    /**
     * 在服务 Socket 绑定到服务时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     */
    public function onBind($server, $serverSocket)
    {
    }

    /**
     * 在服务 Socket 即将暴露调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     */
    public function onExport($server, $serverSocket)
    {
    }

    /**
     * 新连接进入时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param int $fd
     * @param int $reactorId
     */
    public function onConnect($server, $serverSocket, $fd, $reactorId)
    {
    }

    /**
     * 从连接中取得数据时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     */
    public function onReceive($server, $serverSocket, $fd, $reactorId, $data)
    {
    }

    /**
     * 当连接关闭时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param int $fd
     * @param int $reactorId
     */
    public function onClose($server, $serverSocket, $fd, $reactorId)
    {
    }

    /**
     * 报告异常
     *
     * @param \Exception $e
     *
     * @throws \Exception
     */
    protected function reportException(Exception $e)
    {
        try {
            $handler = $this->app->make(ExceptionHandler::class);
        } catch (Exception $ex) {
            // 抛出原始错误
            throw $e;
        }

        $handler->report($e);

        $handler->renderForConsole($this->outputStyle->getOutput(), $e);
    }
}
