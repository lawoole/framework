<?php
namespace Lawoole\Foundation\Providers;

use Illuminate\Foundation\Providers\ArtisanServiceProvider as BaseServiceProvider;
use Lawoole\Foundation\Console\VersionCommand;

class ArtisanServiceProvider extends BaseServiceProvider
{
    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $lawooleCommands = [
        'Version' => 'command.version'
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerCommands(array_merge(
            $this->commands, $this->devCommands, $this->lawooleCommands
        ));
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerVersionCommand()
    {
        $this->app->singleton('command.version', function () {
            return new VersionCommand;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return array_merge(
            array_values($this->commands),
            array_values($this->devCommands),
            array_values($this->lawooleCommands)
        );
    }
}
