<?php
namespace Lawoole\Foundation\Bootstrap;

class RegisterServiceProviders
{
    /**
     * 注册配置中定义的服务提供者
     *
     * @param \Lawoole\Contracts\Foundation\ApplicationInterface $app
     */
    public function bootstrap($app)
    {
        $app->registerConfiguredProviders();
    }
}
