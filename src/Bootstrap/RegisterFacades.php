<?php
namespace Lawoole\Bootstrap;

use Illuminate\Support\Facades\Facade;
use Lawoole\Application;

class RegisterFacades
{
    /**
     * 初始化外观模块
     *
     * @param \Lawoole\Application $app
     */
    public function bootstrap(Application $app)
    {
        Facade::clearResolvedInstances();

        // 设置外观引用的应用实例
        Facade::setFacadeApplication($app);
    }
}
