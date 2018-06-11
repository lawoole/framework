<?php
namespace Lawoole\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Snowflake extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'snowflake';
    }
}
