<?php
namespace Lawoole\Routing;

use BadMethodCallException;
use Illuminate\Contracts\Container\Container;
use Lawoole\Support\Facades\Server;
use Lawoole\Task\Task;
use RuntimeException;

abstract class Controller
{
    /**
     * 服务容器
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    /**
     * 创建控制器
     *
     * @param \Illuminate\Contracts\Container\Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * 推送异步任务
     *
     * @param \Lawoole\Task\Task $task
     *
     * @return \Lawoole\Routing\RequestManager
     */
    protected function pushTask(Task $task)
    {
        if (!in_array(BindRequestManager::class, class_uses($task))) {
            throw new RuntimeException('Task to be pushed must use BindRequestManager trait.');
        }

        $requestManager = $this->app->make(RequestManager::class);

        // 绑定请求管理器
        $task->withRequestManager($requestManager);

        Server::pushTask($task);

        return $requestManager;
    }

    /**
     * 代理动态调用方法
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        throw new BadMethodCallException("Method [{$method}] does not exist.");
    }
}
