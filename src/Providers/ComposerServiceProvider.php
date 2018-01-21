<?php
namespace Lawoole\Providers;

use Illuminate\Support\Composer;
use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * 注册服务提供者
     */
    public function register()
    {
        $this->app->singleton('composer', function ($app) {
            return new Composer($app['files'], $app->basePath());
        });
    }

    /**
     * 获得提供的服务名
     *
     * @return array
     */
    public function provides()
    {
        return ['composer'];
    }
}
