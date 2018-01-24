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

        // 设置外观引用的应用实例
        Facade::setFacadeApplication($app);
    }
}
