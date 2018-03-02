<?php
namespace Lawoole\Foundation\Bootstrap;

use Lawoole\Contracts\Foundation\Application;

class BootProviders
{
    /**
     * 启动服务容器
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     */
    public function bootstrap(Application $app)
    {
        $app->boot();
    }
}
