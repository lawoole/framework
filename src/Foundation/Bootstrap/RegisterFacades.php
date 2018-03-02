<?php
namespace Lawoole\Foundation\Bootstrap;

use Illuminate\Support\Facades\Facade;
use Lawoole\Contracts\Foundation\Application;

class RegisterFacades
{
    /**
     * 初始化外观模块
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     */
    public function bootstrap(Application $app)
    {
        Facade::clearResolvedInstances();

        Facade::setFacadeApplication($app);
    }
}
