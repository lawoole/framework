<?php
namespace Lawoole\Routing;

use Illuminate\Routing\RoutingServiceProvider as BaseServiceProvider;
use Lawoole\Contracts\Routing\ResponseFactory as ResponseFactoryContract;

class RoutingServiceProvider extends BaseServiceProvider
{
    /**
     * 注册响应对象工厂
     */
    protected function registerResponseFactory()
    {
        $this->app->singleton(ResponseFactoryContract::class, function ($app) {
            return new ResponseFactory($app['view'], $app['redirect']);
        });
    }
}
