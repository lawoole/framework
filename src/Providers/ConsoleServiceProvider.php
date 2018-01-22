<?php
namespace Lawoole\Providers;

use Illuminate\Support\AggregateServiceProvider;
use Illuminate\Database\MigrationServiceProvider;

class ConsoleServiceProvider extends AggregateServiceProvider
{
    /**
     * 服务提供者集合
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
