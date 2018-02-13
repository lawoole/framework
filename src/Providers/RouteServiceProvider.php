<?php
namespace Lawoole\Providers;

use Closure;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * 注册服务提供者
     */
    public function boot()
    {
        $this->registerMiddleware($this->app['router']);

        $this->setRootControllerNamespace();

        $this->loadRoutes($this->app['router']);
    }

    /**
     * 载入路由规则
     *
     * @param \Lawoole\Routing\Router $router
     */
    protected function loadRoutes($router)
    {
        if (($routes = $this->app['config']['http.routes']) == null) {
            return;
        }

        $namespace = $this->app['config']['http.namespace'];

        foreach ($routes as $definition) {
            if ($definition instanceof Closure) {
                // 如果是个闭包，直接执行
                call_user_func($definition, $router);

                continue;
            }

            $path = array_pull($definition, 'path');

            if ($namespace && !isset($definition['namespace'])) {
                $definition['namespace'] = $namespace;
            }

            if (file_exists($path)) {
                $router->group($definition, function ($router) use ($path) {
                    include $path;
                });
            }
        }

        $this->app['url']->refreshNameLookups();
        $this->app['url']->refreshActionLookups();
    }

    /**
     * 设置控制器根命名空间
     */
    protected function setRootControllerNamespace()
    {
        if ($namespace = $this->app['config']['http.namespace']) {
            $this->app['url']->setRootControllerNamespace($namespace);
        }
    }

    /**
     * 注册中间件
     *
     * @param \Lawoole\Routing\Router $router
     */
    protected function registerMiddleware($router)
    {
        // 全局中间件
        $globalMiddleware = $this->app['config']['http.middleware'];

        if ($globalMiddleware) {
            $router->middleware($globalMiddleware);
        }

        // 路由中间件
        $routeMiddleware = $this->app['config']['http.route_middleware'];

        if ($routeMiddleware) {
            $router->routeMiddleware($routeMiddleware);
        }
    }
}
