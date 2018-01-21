<?php
namespace Lawoole\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Swoole extends Facade
{
    /**
     * 获得服务名
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'swoole';
    }
}
