<?php
namespace Lawoole\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Illuminate\Console\Command
 */
class Command extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'console.command';
    }
}
