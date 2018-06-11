<?php
namespace Lawoole\Snowflake;

use Illuminate\Support\ServiceProvider;

class SnowflakeServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton('snowflake', function () {
            return new Snowflake;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['snowflake'];
    }
}
