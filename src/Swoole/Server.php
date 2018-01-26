<?php
namespace Lawoole\Swoole;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use IteratorAggregate;
use RuntimeException;
use Swoole\Server as SwooleServer;

class Server implements IteratorAggregate
{
    use HasEventHandlers;

    /**
     * 进程类型：主进程
     */
    const MASTER = 1;

    /**
     * 进程类型：管理进程
     */
    const MANAGER = 2;

    /**
     * 进程类型：工作进程
     */
    const WORKER = 3;

    /**
     * Swoole 服务对象
     *
     * @var \Swoole\Server
     */
    protected $swooleServer;

    /**
     * 服务配置
     *
     * @var array
     */
    protected $config;

    /**
     * Unix Sock 文件路径
     *
     * @var string
     */
    protected $unixSock;

    /**
     * 是否正在提供服务
     *
     * @var bool
     */
    protected $serving = false;

    /**
     * 默认服务 Socket
     *
     * @var \Lawoole\Swoole\ServerSocket
     */
    protected $serverSocket;

    /**
     * 服务 Socket 集合
     *
     * @var \Lawoole\Swoole\ServerSocket[]
     */
    protected $serverSockets = [];

    /**
     * 自定义进程集合
     *
     * @var \Lawoole\Swoole\Process[]
     */
    protected $processes = [];

    /**
     * Swoole 服务选项
     *
     * @var array
     */
    protected $options = [
        'reload_async' => true
    ];

    /**
     * 可用事件回调
     *
     * @var array
     */
    protected $serverEvents = [
        'Start', 'Shutdown', 'ManagerStart', 'ManagerStop',
        'WorkerStart', 'WorkerStop', 'WorkerExit', 'WorkerError',
        'Task', 'Finish', 'PipeMessage', 'BufferFull', 'BufferEmpty',
        'Connect', 'Close', 'Receive', 'Packet'
    ];

    /**
     * 创建服务
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;

        // 初始化服务
        $this->initialize($config);
    }

    /**
     * 初始化服务
     *
     * @param array $config
     */
    protected function initialize(array $config)
    {
        // 设置 Unix Sock 文件
        $this->unixSock = Arr::get($config, 'unix_sock', function () {
            return $this->generateUnixSock();
        });

        // 创建 Swoole 服务对象
        $this->swooleServer = $this->createSwooleServer();

            // 配置选项
        $options = Arr::get($config, 'options');

        if ($options) {
            $this->setOptions($options);
        }

        // 注册事件回调
        $this->registerEventCallbacks($this->serverEvents);
    }

    /**
     * 创建 Swoole 服务
     *
     * @return \Swoole\Server
     */
    protected function createSwooleServer()
    {
        return new SwooleServer($this->getUnixSock(), 0, SWOOLE_PROCESS, SWOOLE_SOCK_UNIX_STREAM);
    }

    /**
     * 创建默认服务 Socket
     *
     * @param \Swoole\Server $swooleServer
     */
    protected function createDefaultServerSocket($swooleServer)
    {
        $unixSock = $this->getUnixSock();

        $serverSocket = new UnixServerSocket($unixSock);

        $this->serverSocket = $serverSocket;
        $this->serverSockets[$unixSock] = $serverSocket;

        $serverSocket->bindToServer($this, Arr::first($swooleServer->ports));
    }

    /**
     * 获得 Swoole 服务对象
     *
     * @return \Swoole\Server
     */
    public function getSwooleServer()
    {
        return $this->swooleServer;
    }

    /**
     * 生成 Unix Sock 文件路径
     *
     * @return string
     */
    protected function generateUnixSock()
    {
        return '/tmp/lawoole_swoole_'.Str::random(8).'.sock';
    }

    /**
     * 获得 Unix Sock 文件路径
     *
     * @return string
     */
    public function getUnixSock()
    {
        return $this->unixSock;
    }

    /**
     * 设置服务选项
     *
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        if ($this->serving) {
            throw new RuntimeException('Options cannot be set while the server is serving.');
        }

        $this->options = array_diff_key($this->options, $options) + $options;

        $this->swooleServer->set($this->options);
    }

    /**
     * 获得服务选项
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * 增加端口监听
     *
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     */
    public function listen(ServerSocket $serverSocket)
    {
        if ($this->serving) {
            throw new RuntimeException('Cannot listen to port while the server is serving.');
        }

        // 服务 Socket 已经绑定过服务，需要进行检查
        if ($server = $serverSocket->getServer()) {
            if ($server !== $this) {
                throw new RuntimeException('The server socket has been bound to anther server.');
            }

            return;
        }

        $host = $serverSocket->getHost();
        $port = $serverSocket->getPort();
        $type = $serverSocket->getType();

        $swooleServerPort = $this->swooleServer->listen($host, $port, $type);

        if ($type != SWOOLE_SOCK_UNIX_DGRAM && $type != SWOOLE_SOCK_UNIX_STREAM) {
            $identify = $port;
        } else {
            $identify = $host;
        }

        $this->serverSockets[$identify] = $serverSocket;

        $serverSocket->bindToServer($this, $swooleServerPort);
    }

    /**
     * 添加自定义进程
     *
     * @param \Lawoole\Swoole\Process $process
     */
    public function addProcess(Process $process)
    {
        if ($this->serving) {
            throw new RuntimeException('Cannot add process while the server is serving.');
        }

        // 进程已经绑定过服务，需要进行检查
        if ($server = $process->getServer()) {
            if ($server !== $this) {
                throw new RuntimeException('The server socket has been bound to anther server.');
            }

            return;
        }

        $this->processes[] = $process;

        $this->swooleServer->addProcess($process->getSwooleProcess());

        $process->bindToServer($this);
    }

    /**
     * 判断是否正在提供服务
     *
     * @return bool
     */
    public function isServing()
    {
        return $this->serving;
    }

    /**
     * 获得主进程 PID
     *
     * @return int
     */
    public function getMasterPId()
    {
        return $this->swooleServer->master_pid;
    }

    /**
     * 获得管理进程 PID
     *
     * @return int
     */
    public function getManagerPId()
    {
        return $this->swooleServer->manager_pid;
    }

    /**
     * 获得当前工作进程 ID
     *
     * @return int
     */
    public function getWorkerId()
    {
        return $this->swooleServer->worker_id;
    }

    /**
     * 获得当前工作进程 PID
     *
     * @return int
     */
    public function getWorkerPid()
    {
        return $this->swooleServer->worker_pid;
    }

    /**
     * 判断当前进程是否为任务工作进程
     *
     * @return bool
     */
    public function isTaskWorker()
    {
        return $this->swooleServer->taskworker;
    }

    /**
     * 获得当前服务所有连接的迭代器
     *
     * @return \Swoole\Connection\Iterator
     */
    public function getConnectionIterator()
    {
        return $this->swooleServer->connections;
    }

    /**
     * 启动并提供服务
     */
    public function serve()
    {
        $this->serving = true;

        $this->dispatchEvent('Launch', $this);

        // 触发服务 Socket 暴露
        foreach ($this->serverSockets as $serverSocket) {
            $serverSocket->export();
        }

        $this->swooleServer->start();

        $this->serving = false;
    }

    /**
     * 停止服务
     */
    public function stop()
    {
        if ($this->serving == false) {
            return;
        }

        $this->swooleServer->shutdown();
    }

    /**
     * 重启所有工作进程
     *
     * @param bool $onlyTask 是否只重启任务工作进程
     *
     * @return bool
     */
    public function reload($onlyTask = false)
    {
        return $this->swooleServer->reload($onlyTask);
    }

    /**
     * 停止工作进程
     *
     * @param int $workerId
     * @param bool $waitEvent
     *
     * @return bool
     */
    public function stopWorker($workerId = -1, $waitEvent = false)
    {
        return $this->swooleServer->stop($workerId, $waitEvent);
    }

    /**
     * 推送任务
     *
     * @param mixed $data
     * @param int $dstWorkerId
     *
     * @return int
     */
    public function pushTask($data, $dstWorkerId = -1)
    {
        return $this->swooleServer->task($data, $dstWorkerId);
    }

    /**
     * 推送任务
     *
     * @param mixed $data
     * @param float $timeout
     * @param int $dstWorkerId
     *
     * @return int
     */
    public function pushTaskWait($data, $timeout = 0.5, $dstWorkerId = -1)
    {
        return $this->swooleServer->taskwait($data, $timeout, $dstWorkerId);
    }

    /**
     * 推送任务
     *
     * @param mixed $tasks
     * @param float $timeout
     *
     * @return array
     */
    public function pushTaskWaitMulti(array $tasks, $timeout = 0.5)
    {
        return $this->swooleServer->taskWaitMulti($tasks, $timeout);
    }

    /**
     * 推送进程间管道消息
     *
     * @param mixed $data
     * @param int $dstWorkerId
     *
     * @return bool
     */
    public function pushPipeMessage($data, $dstWorkerId)
    {
        return $this->swooleServer->sendMessage($data, $dstWorkerId);
    }

    /**
     * 推送任务响应
     *
     * @param mixed $data
     */
    public function pushTaskResponse($data)
    {
        $this->swooleServer->finish($data);
    }

    /**
     * 向客户端发送数据
     *
     * @param int $fd
     * @param string $data
     * @param int $serverSocket
     *
     * @return bool
     */
    public function send($fd, $data, $serverSocket = -1)
    {
        return $this->swooleServer->send($fd, $data, $serverSocket);
    }

    /**
     * 向指定客户端发送 Udp 数据包
     *
     * @param string $ip
     * @param int $port
     * @param string $data
     * @param int $serverSocket
     *
     * @return bool
     */
    public function sendUdpPacket($ip, $port, $data, $serverSocket = -1)
    {
        return $this->swooleServer->sendto($ip, $port, $data, $serverSocket);
    }

    /**
     * 向客户端发送文件中的数据
     *
     * @param int $fd
     * @param string $filename
     * @param int $offset
     * @param int $length
     *
     * @return bool
     */
    public function sendFile($fd, $filename, $offset = 0, $length = 0)
    {
        return $this->swooleServer->sendfile($fd, $filename, $offset, $length);
    }

    /**
     * 获得服务当前统计数据
     *
     * @return array
     */
    public function stats()
    {
        return $this->swooleServer->stats();
    }

    /**
     * 判断连接是否存在
     *
     * @param int $fd
     *
     * @return bool
     */
    public function exist($fd)
    {
        return $this->swooleServer->exist($fd);
    }

    /**
     * 获得连接信息
     *
     * @param int $fd
     * @param int $fromId
     * @param bool $checkConnection
     *
     * @return array
     */
    public function getClientInfo($fd, $fromId, $checkConnection = false)
    {
        return $this->swooleServer->getClientInfo($fd, $fromId, $checkConnection);
    }

    /**
     * 注册事件回调
     */
    protected function registerStartCallback()
    {
        $this->swooleServer->on('Start', function () {
            $this->dispatchEvent('Start', $this);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerShutdownCallback()
    {
        $this->swooleServer->on('Shutdown', function () {
            $this->dispatchEvent('Shutdown', $this);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerManagerStartCallback()
    {
        $this->swooleServer->on('ManagerStart', function () {
            $this->dispatchEvent('ManagerStart', $this);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerManagerStopCallback()
    {
        $this->swooleServer->on('ManagerStop', function () {
            $this->dispatchEvent('ManagerStop', $this);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerWorkerStartCallback()
    {
        $this->swooleServer->on('WorkerStart', function ($server, $workerId) {
            $this->dispatchEvent('WorkerStart', $this, $workerId);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerWorkerStopCallback()
    {
        $this->swooleServer->on('WorkerStop', function ($server, $workerId) {
            $this->dispatchEvent('WorkerStop', $this, $workerId);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerWorkerExitCallback()
    {
        $this->swooleServer->on('WorkerExit', function ($server, $workerId) {
            $this->dispatchEvent('WorkerExit', $this, $workerId);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerWorkerErrorCallback()
    {
        $this->swooleServer->on('WorkerError', function ($server, $workerId, $workerPid, $exitCode, $signal) {
            $this->dispatchEvent('WorkerError', $this, $workerId, $workerPid, $exitCode, $signal);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerTaskCallback()
    {
        $this->swooleServer->on('Task', function ($server, $taskId, $srcWorkerId, $data) {
            $this->dispatchEvent('Task', $this, $taskId, $srcWorkerId, $data);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerFinishCallback()
    {
        $this->swooleServer->on('Finish', function ($server, $taskId, $data) {
            $this->dispatchEvent('Finish', $this, $taskId, $data);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerPipeMessageCallback()
    {
        $this->swooleServer->on('PipeMessage', function ($server, $srcWorkerId, $data) {
            $this->dispatchEvent('PipeMessage', $this, $srcWorkerId, $data);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerBufferFullCallback()
    {
        $this->swooleServer->on('BufferFull', function ($server, $fd) {
            $this->dispatchEvent('BufferFull', $this, $this->serverSocket, $fd);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerBufferEmptyCallback()
    {
        $this->swooleServer->on('BufferEmpty', function ($server, $fd) {
            $this->dispatchEvent('BufferEmpty', $this, $this->serverSocket, $fd);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerConnectCallback()
    {
        $this->swooleServer->on('Connect', function ($server, $fd, $reactorId) {
            $this->dispatchEvent('Connect', $this, $this->serverSocket, $fd, $reactorId);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerCloseCallback()
    {
        $this->swooleServer->on('Close', function ($server, $fd, $reactorId) {
            $this->dispatchEvent('Close', $this, $this->serverSocket, $fd, $reactorId);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerReceiveCallback()
    {
        $this->swooleServer->on('Receive', function ($server, $fd, $reactorId, $data) {
            $this->dispatchEvent('Receive', $this, $this->serverSocket, $fd, $reactorId, $data);
        });
    }

    /**
     * 注册事件回调
     */
    protected function registerPacketCallback()
    {
        $this->swooleServer->on('Packet', function ($server, $data, $clientInfo) {
            $this->dispatchEvent('Packet', $this, $this->serverSocket, $data, $clientInfo);
        });
    }

    /**
     * 获得当前服务所有连接的迭代器
     *
     * @return \Swoole\Connection\Iterator
     */
    public function getIterator()
    {
        return $this->getConnectionIterator();
    }
}
