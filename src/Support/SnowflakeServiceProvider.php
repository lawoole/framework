<?php
namespace Lawoole\Support;

use Illuminate\Support\ServiceProvider;

class SnowflakeServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register()
    {
        $this->app->singleton('snowflake', function () {
            return new Snowflake;
        });
    }
}
