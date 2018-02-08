<?php
namespace Lawoole\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Server extends Facade
{
    /**
     * 获得服务名
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'server';
    }

    /**
     * 判断是否运行在 Swoole 中
     *
     * @return bool
     */
    public static function inSwoole()
    {
        return static::$app->bound('server.swoole');
    }
}
