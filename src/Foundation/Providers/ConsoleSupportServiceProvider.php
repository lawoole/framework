<?php
namespace Lawoole\Foundation\Providers;

use Illuminate\Database\MigrationServiceProvider;
use Illuminate\Foundation\Providers\ComposerServiceProvider;
use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider as BaseServiceProvider;
use Lawoole\Console\OutputStyle;

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

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        parent::register();

        $this->registerOutputStyle();
    }

    /**
     * Register the styled output.
     */
    protected function registerOutputStyle()
    {
        $this->app->singleton('console.output.style', function ($app) {
            return new OutputStyle($app['console.input'], $app['console.output']);
        });
    }
}
