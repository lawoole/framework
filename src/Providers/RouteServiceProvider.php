<?php
namespace Lawoole\Providers;

use Closure;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * 启动服务提供者
     */
    public function boot()
    {
        // 载入路由规则
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

        foreach ($routes as $route) {
            if ($route instanceof Closure) {
                // 如果是个闭包，直接执行
                call_user_func($route, $router);

                continue;
            }

            if (is_array($route)) {
                $path = array_pull($route, 'path');
            } else {
                $path = $route;
                $route = [];
            }

            if (file_exists($path)) {
                $router->group($route, function ($router) use ($path) {
                    include $path;
                });
            }
        }
    }
}
