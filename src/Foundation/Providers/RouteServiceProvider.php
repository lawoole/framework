<?php
namespace Lawoole\Foundation\Providers;

use Closure;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * 启动服务
     */
    public function boot()
    {
        $this->setRootControllerNamespace();

        $this->loadRoutes($this->app['router']);

        $this->app->booted(function () {
            $this->app['router']->getRoutes()->refreshNameLookups();
            $this->app['router']->getRoutes()->refreshActionLookups();
        });
    }

    /**
     * 载入路由规则
     *
     * @param \Illuminate\Routing\Router $router
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

            if (is_string($definition)) {
                $definition = [
                    'path' => $definition
                ];
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
}
