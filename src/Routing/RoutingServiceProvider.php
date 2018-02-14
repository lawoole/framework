<?php
namespace Lawoole\Routing;

use Illuminate\Support\ServiceProvider;

class RoutingServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register()
    {
        $this->registerRouter();

        $this->registerUrlGenerator();

        $this->registerControllerDispatcher();
    }

    /**
     * 注册路由器
     */
    protected function registerRouter()
    {
        $this->app->singleton('router', function ($app) {
            return new Router($app);
        });
    }

    /**
     * 注册 Url 生成器
     */
    protected function registerUrlGenerator()
    {
        $this->app->singleton('url', function ($app) {
            $url = new UrlGenerator($app, $app['router']);

            $app->rebinding('router', function ($app, $router) {
                $app['url']->setRouter($router);
            });

            return $url;
        });
    }

    /**
     * 注册控制调度器
     */
    protected function registerControllerDispatcher()
    {
        $this->app->singleton('router.dispatcher', function ($app) {
            return new ControllerDispatcher($app['router']);
        });
    }

    /**
     * 获得提供的服务名
     *
     * @return array
     */
    public function provides()
    {
        return ['router', 'router.dispatcher'];
    }
}
