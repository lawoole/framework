<?php
namespace Lawoole\Providers;

use Illuminate\Database\MigrationServiceProvider;
use Illuminate\Support\AggregateServiceProvider;

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
