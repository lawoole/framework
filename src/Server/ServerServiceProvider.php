<?php
namespace Lawoole\Server;

use Illuminate\Support\ServiceProvider;
use Lawoole\Server\Console\ReloadCommand;
use Lawoole\Server\Console\ShutdownCommand;
use Lawoole\Server\Console\StartCommand;

class ServerServiceProvider extends ServiceProvider
{
    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        'ServerStart'    => 'command.server.start',
        'ServerShutdown' => 'command.server.shutdown',
        'ServerReload'   => 'command.server.reload',
    ];

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton('server.factory', function ($app) {
            return new ServerFactory($app, $app['console.output']);
        });

        // By default, the server factory creates a server instance based on the
        // 'server' configuration. We can easily get it through the IoC container
        // or the server facade.
        $this->app->singleton('server', function ($app) {
            return $app['server.factory']->make($app['config']['server']);
        });

        // We can get a Swoole server instance from server, this allows us to easily
        // perform in-depth operations.
        $this->app->singleton('server.swoole', function ($app) {
            return $app['server']->getSwooleServer();
        });

        $this->registerCommands($this->commands);
    }

    /**
     * Register the given commands.
     *
     * @param array $commands
     */
    protected function registerCommands(array $commands)
    {
        foreach (array_keys($commands) as $command) {
            call_user_func([$this, "register{$command}Command"]);
        }

        $this->commands(array_values($commands));
    }

    /**
     * Register the command.
     */
    protected function registerServerStartCommand()
    {
        $this->app->singleton('command.server.start', function () {
            return new StartCommand;
        });
    }

    /**
     * Register the command.
     */
    protected function registerServerShutdownCommand()
    {
        $this->app->singleton('command.server.shutdown', function () {
            return new ShutdownCommand;
        });
    }

    /**
     * Register the command.
     */
    protected function registerServerReloadCommand()
    {
        $this->app->singleton('command.server.reload', function () {
            return new ReloadCommand;
        });
    }
}
