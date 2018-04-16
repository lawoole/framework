<?php
namespace Lawoole\Homer;

use Illuminate\Support\ServiceProvider;

class HomerServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register()
    {
        $this->registerHomer();

        $this->registerDispatcher();

        $this->registerServerSocketAliases();
    }

    /**
     * 注册 Homer 管理器
     */
    protected function registerHomer()
    {
        $this->app->singleton('homer', function ($app) {
            return new HomerManager($app, $app['config']['homer']);
        });
    }

    /**
     * 注册调用分发器
     */
    protected function registerDispatcher()
    {
        $this->app->singleton('homer.dispatcher', function () {
            return new Dispatcher;
        });
    }

    /**
     * 注册服务 Socket 类型别名
     */
    protected function registerServerSocketAliases()
    {
        $this->app->instance('server.protocol.whisper', 'tcp');
    }

    /**
     * 启动服务
     */
    public function boot()
    {
        $this->app->make('homer')->boot($this->app);
    }
}
