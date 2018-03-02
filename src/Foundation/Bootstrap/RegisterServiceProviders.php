<?php
namespace Lawoole\Foundation\Bootstrap;

use Lawoole\Contracts\Foundation\Application;

class RegisterServiceProviders
{
    /**
     * 注册配置中定义的服务提供者
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     */
    public function bootstrap(Application $app)
    {
        $app->registerConfiguredProviders();
    }
}
