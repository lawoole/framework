<?php
namespace Lawoole\Server;

use Illuminate\Support\ServiceProvider;
use Lawoole\Server\Commands\ReloadCommand;
use Lawoole\Server\Commands\ShutdownCommand;
use Lawoole\Server\Commands\StartCommand;

class ServerServiceProvider extends ServiceProvider
{
    /**
     * Server 命令
     *
     * @var array
     */
    protected $commands = [
        'ServerStart'  => 'command.server.start',
        'ServerDown'   => 'command.server.down',
        'ServerReload' => 'command.server.reload',
    ];

    /**
     * 注册服务
     */
    public function register()
    {
        $this->app->singleton('server', function ($app) {
            $config = $app->make('config')->get('server');

            $driver = $config['driver'] ?? 'tcp';

            return new ServerManager($app, $driver, $config);
        });

        $this->app->singleton('server.swoole', function ($app) {
            return $app->make('server')->getSwooleServer();
        });

        $this->registerCommands($this->commands);
    }

    /**
     * 注册命令
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
     * 注册命令
     */
    protected function registerServerStartCommand()
    {
        $this->app->singleton('command.server.start', function () {
            return new StartCommand;
        });
    }

    /**
     * 注册命令
     */
    protected function registerServerShutdownCommand()
    {
        $this->app->singleton('command.server.shutdown', function () {
            return new ShutdownCommand;
        });
    }

    /**
     * 注册命令
     */
    protected function registerServerReloadCommand()
    {
        $this->app->singleton('command.server.reload', function () {
            return new ReloadCommand;
        });
    }
}
