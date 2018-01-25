<?php
namespace Lawoole\Foundation\Bootstrap;

use Illuminate\Support\Facades\Facade;

class RegisterFacades
{
    /**
     * 初始化外观模块
     *
     * @param \Lawoole\Contracts\Foundation\ApplicationInterface $app
     */
    public function bootstrap($app)
    {
        Facade::clearResolvedInstances();

        // 设置外观引用的应用实例
        Facade::setFacadeApplication($app);
    }
}
