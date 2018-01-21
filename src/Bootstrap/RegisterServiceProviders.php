<?php
namespace Lawoole\Bootstrap;

use Lawoole\Application;

class RegisterServiceProviders
{
    /**
     * 注册配置中定义的服务提供者
     *
     * @param \Lawoole\Application $app
     */
    public function bootstrap(Application $app)
    {
        $app->registerConfiguredProviders();
    }
}
