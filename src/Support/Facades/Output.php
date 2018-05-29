<?php
namespace Lawoole\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Lawoole\Console\OutputStyle;

/**
 * @mixin \Lawoole\Console\OutputStyle
 */
class Output extends Facade
{
    /**
     * 获得容器注册名
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return OutputStyle::class;
    }
}
