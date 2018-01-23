<?php
namespace Lawoole\Log;

use Illuminate\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{
    /**
     * 注册服务提供者
     */
    public function register()
    {
        $this->app->singleton('log', function ($app) {
            return new LogManager($app, $app['config']['log'], $app['events']);
        });
    }

    /**
     * 获得提供的服务名
     *
     * @return array
     */
    public function provides()
    {
        return ['log'];
    }
}
