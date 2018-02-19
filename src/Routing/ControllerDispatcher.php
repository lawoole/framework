<?php
namespace Lawoole\Routing;

use FastRoute\Dispatcher as FastRouteDispatcher;
use FastRoute\Dispatcher\GroupCountBased as DispatcherGroupCount;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ControllerDispatcher
{
    /**
     * 路由器
     *
     * @var \Lawoole\Routing\Router
     */
    protected $router;

    /**
     * FastRoute 调度器
     *
     * @var \FastRoute\Dispatcher
     */
    protected $fastDispatcher;

    /**
     * 创建控制调度器
     *
     * @param \Lawoole\Routing\Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;

        $this->updateRouteCollector();
    }

    /**
     * 更新路由记录
     */
    public function updateRouteCollector()
    {
        $this->fastDispatcher = new DispatcherGroupCount(
            $this->router->getRouteCollector()->getData()
        );
    }

    /**
     * 执行调度
     *
     * @param string $method
     * @param string $pathInfo
     *
     * @return array
     */
    public function dispatch($method, $pathInfo)
    {
        return $this->handleDispatcherResult(
            $this->fastDispatcher->dispatch($method, $pathInfo)
        );
    }

    /**
     * 处理调度结果
     *
     * @param array $routeInfo
     *
     * @return array
     */
    protected function handleDispatcherResult($routeInfo)
    {
        switch ($routeInfo[0]) {
            case FastRouteDispatcher::NOT_FOUND:
                throw new NotFoundHttpException;
            case FastRouteDispatcher::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedHttpException($routeInfo[1]);
        }

        $route = $this->router->getRoute($routeInfo[1]);

        if (isset($route['arguments'])) {
            $arguments = array_merge((array) $route['arguments'], $routeInfo[2]);
        } else {
            $arguments = $routeInfo[2];
        }

        return [
            'handler'   => $routeInfo[1],
            'route'     => $route,
            'action'    => $route['action'],
            'arguments' => $arguments
        ];
    }

    /**
     * 定义全局调度中间件
     *
     * @param mixed $middleware
     *
     * @return $this
     */
    public function middleware($middleware)
    {
        $this->router->middleware($middleware);

        return $this;
    }

    /**
     * 获得全局调度中间件集合
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->router->getMiddleware();
    }

    /**
     * 定义路由调度中间件
     *
     * @param array $middleware
     *
     * @return $this
     */
    public function routeMiddleware(array $middleware)
    {
        $this->router->routeMiddleware($middleware);

        return $this;
    }

    /**
     * 获得路由调度中间件
     *
     * @return array
     */
    public function getRouteMiddleware()
    {
        return $this->router->getRouteMiddleware();
    }
}
