<?php
namespace Lawoole\Bootstrap;

use Lawoole\Application;

class BootProviders
{
    /**
     * 启动服务容器
     *
     * @param \Lawoole\Application $app
     */
    public function bootstrap(Application $app)
    {
        $app->boot();
    }
}
