<?php
namespace Lawoole\Foundation\Bootstrap;

class BootProviders
{
    /**
     * 启动服务容器
     *
     * @param \Lawoole\Contracts\Foundation\ApplicationInterface $app
     */
    public function bootstrap($app)
    {
        $app->boot();
    }
}
