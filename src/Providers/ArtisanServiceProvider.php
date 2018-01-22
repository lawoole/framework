<?php
namespace Lawoole\Providers;

use Illuminate\Cache\Console\CacheTableCommand;
use Illuminate\Cache\Console\ClearCommand as CacheClearCommand;
use Illuminate\Cache\Console\ForgetCommand as CacheForgetCommand;
use Illuminate\Console\Scheduling\ScheduleFinishCommand;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Illuminate\Database\Console\Factories\FactoryMakeCommand;
use Illuminate\Database\Console\Migrations\FreshCommand as MigrateFreshCommand;
use Illuminate\Database\Console\Migrations\InstallCommand as MigrateInstallCommand;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand as MigrateRefreshCommand;
use Illuminate\Database\Console\Migrations\ResetCommand as MigrateResetCommand;
use Illuminate\Database\Console\Migrations\RollbackCommand as MigrateRollbackCommand;
use Illuminate\Database\Console\Migrations\StatusCommand as MigrateStatusCommand;
use Illuminate\Database\Console\Seeds\SeedCommand;
use Illuminate\Database\Console\Seeds\SeederMakeCommand;
use Illuminate\Support\ServiceProvider;
use Lawoole\Console\Commands\AppNameCommand;
use Lawoole\Console\Commands\DownCommand;
use Lawoole\Console\Commands\UpCommand;
use Lawoole\Console\Commands\ViewClearCommand;
use Lawoole\Server\Commands\ShutdownCommand;
use Lawoole\Server\Commands\StartCommand;

class ArtisanServiceProvider extends ServiceProvider
{
    /**
     * 所有支持的命令
     *
     * @var array
     */
    protected $commands = [
        'Up'              => 'command.up',
        'Down'            => 'command.down',
        'Start'           => 'command.start',
        'Shutdown'        => 'command.shutdown',
        'AppName'         => 'command.app.name',
        'CacheClear'      => 'command.cache.clear',
        'CacheForget'     => 'command.cache.forget',
        'CacheTable'      => 'command.cache.table',
        'FactoryMake'     => 'command.factory.make',
        'Migrate'         => 'command.migrate',
        'MigrateFresh'    => 'command.migrate.fresh',
        'MigrateInstall'  => 'command.migrate.install',
        'MigrateMake'     => 'command.migrate.make',
        'MigrateRefresh'  => 'command.migrate.refresh',
        'MigrateReset'    => 'command.migrate.reset',
        'MigrateRollback' => 'command.migrate.rollback',
        'MigrateStatus'   => 'command.migrate.status',
        'ScheduleRun'     => 'command.schedule.run',
        'ScheduleFinish'  => 'command.schedule.finish',
        'Seed'            => 'command.seed',
        'SeederMake'      => 'command.seeder.make',
        'ViewClear'       => 'command.view.clear',
    ];

    /**
     * 注册服务提供者
     */
    public function register()
    {
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
            call_user_func_array([$this, "register{$command}Command"], []);
        }
    }

    /**
     * 注册命令
     */
    protected function registerUpCommand()
    {
        $this->app->singleton('command.up', function () {
            return new UpCommand;
        });
    }

    /**
     * 注册命令
     */
    protected function registerDownCommand()
    {
        $this->app->singleton('command.down', function () {
            return new DownCommand;
        });
    }

    /**
     * 注册命令
     */
    protected function registerStartCommand()
    {
        $this->app->singleton('command.start', function () {
            return new StartCommand;
        });
    }

    /**
     * 注册命令
     */
    protected function registerShutdownCommand()
    {
        $this->app->singleton('command.shutdown', function () {
            return new ShutdownCommand;
        });
    }

    /**
     * 注册命令
     */
    protected function registerAppNameCommand()
    {
        $this->app->singleton('command.app.name', function () {
            return new AppNameCommand;
        });
    }

    /**
     * 注册命令
     */
    protected function registerCacheClearCommand()
    {
        $this->app->singleton('command.cache.clear', function ($app) {
            return new CacheClearCommand($app['cache'], $app['files']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerCacheForgetCommand()
    {
        $this->app->singleton('command.cache.forget', function ($app) {
            return new CacheForgetCommand($app['cache']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerCacheTableCommand()
    {
        $this->app->singleton('command.cache.table', function ($app) {
            return new CacheTableCommand($app['files'], $app['composer']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerFactoryMakeCommand()
    {
        $this->app->singleton('command.factory.make', function ($app) {
            return new FactoryMakeCommand($app['files']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerMigrateCommand()
    {
        $this->app->singleton('command.migrate', function ($app) {
            return new MigrateCommand($app['migrator']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerMigrateFreshCommand()
    {
        $this->app->singleton('command.migrate.fresh', function () {
            return new MigrateFreshCommand;
        });
    }

    /**
     * 注册命令
     */
    protected function registerMigrateInstallCommand()
    {
        $this->app->singleton('command.migrate.install', function ($app) {
            return new MigrateInstallCommand($app['migration.repository']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerMigrateMakeCommand()
    {
        $this->app->singleton('command.migrate.make', function ($app) {
            return new MigrateMakeCommand($app['migration.creator'], $app['composer']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerMigrateRefreshCommand()
    {
        $this->app->singleton('command.migrate.refresh', function () {
            return new MigrateRefreshCommand;
        });
    }

    /**
     * 注册命令
     */
    protected function registerMigrateResetCommand()
    {
        $this->app->singleton('command.migrate.reset', function ($app) {
            return new MigrateResetCommand($app['migrator']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerMigrateRollbackCommand()
    {
        $this->app->singleton('command.migrate.rollback', function ($app) {
            return new MigrateRollbackCommand($app['migrator']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerMigrateStatusCommand()
    {
        $this->app->singleton('command.migrate.status', function ($app) {
            return new MigrateStatusCommand($app['migrator']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerScheduleRunCommand()
    {
        $this->app->singleton('command.schedule.run', function ($app) {
            return new ScheduleRunCommand($app['schedule']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerScheduleFinishCommand()
    {
        $this->app->singleton('command.schedule.finish', function ($app) {
            return new ScheduleFinishCommand($app['schedule']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerSeedCommand()
    {
        $this->app->singleton('command.seed', function ($app) {
            return new SeedCommand($app['db']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerSeederMakeCommand()
    {
        $this->app->singleton('command.seeder.make', function ($app) {
            return new SeederMakeCommand($app['files'], $app['composer']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerViewClearCommand()
    {
        $this->app->singleton('command.view.clear', function ($app) {
            return new ViewClearCommand($app['files']);
        });
    }

    /**
     * 获得提供的服务名
     *
     * @return array
     */
    public function provides()
    {
        return array_values($this->commands);
    }
}
