<?php
namespace Lawoole\Foundation\Providers;

use Illuminate\Database\MigrationServiceProvider;
use Illuminate\Foundation\Providers\ComposerServiceProvider;
use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider as BaseServiceProvider;

class ConsoleSupportServiceProvider extends BaseServiceProvider
{
    /**
     * The provider class names.
     *
     * @var array
     */
    protected $providers = [
        ArtisanServiceProvider::class,
        ComposerServiceProvider::class,
        MigrationServiceProvider::class,
        ScheduleServiceProvider::class,
    ];
}
