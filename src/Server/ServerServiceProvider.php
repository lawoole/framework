<?php
namespace Lawoole\Server;

use Illuminate\Support\ServiceProvider;
use Lawoole\Server\Commands\ReloadCommand;
use Lawoole\Server\Commands\ShutdownCommand;
use Lawoole\Server\Commands\StartCommand;

class ServerServiceProvider extends ServiceProvider
{
    /**
     * 支持的命令
     *
     * @var array
     */
    protected $commands = [
        'Start'    => 'command.start',
        'Shutdown' => 'command.shutdown',
        'Reload'   => 'command.reload',
    ];

    /**
     * 注册服务
     */
    public function register()
    {
        $this->app->singleton('server.manager', function ($app) {
            return new ServerManager($app);
        });

        $this->app->singleton('server', function ($app) {
            return $app->make('server.manager')->getServer();
        });

        $this->app->singleton('server.swoole', function ($app) {
            return $app->make('server')->getSwooleServer();
        });

        $this->registerCommands($this->commands);
    }

    /**
     * 获得提供的服务名
     *
     * @return array
     */
    public function provides()
    {
        return ['server', 'server.manager', 'server.swoole'];
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


}
