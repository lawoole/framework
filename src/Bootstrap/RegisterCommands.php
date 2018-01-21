<?php
namespace Lawoole\Bootstrap;

use Illuminate\Cache\Console\CacheTableCommand;
use Illuminate\Cache\Console\ClearCommand as CacheClearCommand;
use Illuminate\Cache\Console\ForgetCommand as CacheForgetCommand;
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
use Illuminate\Session\Console\SessionTableCommand;
use Lawoole\Application;
use Lawoole\Console\Commands\AppDownCommand;
use Lawoole\Console\Commands\AppNameCommand;
use Lawoole\Console\Commands\AppUpCommand;
use Lawoole\Console\Commands\ViewClearCommand;

class RegisterCommands
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Application
     */
    protected $app;

    /**
     * 所有支持的命令
     *
     * @var array
     */
    protected $commands = [
        'AppName'         => 'command.app.name',
        'AppUp'           => 'command.app.up',
        'AppDown'         => 'command.app.down',
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
        'Seed'            => 'command.seed',
        'SeederMake'      => 'command.seeder.make',
        'SessionTable'    => 'command.session.table',
        'ViewClear'       => 'command.view.clear',
    ];

    /**
     * 注册命令
     *
     * @param \Lawoole\Application $app
     */
    public function bootstrap(Application $app)
    {
        $this->app = $app;

        $this->registerCommands($this->commands);
    }

    /**
     * 注册命令
     *
     * @param array $commands
     *
     * @return void
     */
    protected function registerCommands(array $commands)
    {
        foreach (array_keys($commands) as $command) {
            call_user_func([$this, "register{$command}Command"]);
        }
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
    protected function registerAppUpCommand()
    {
        $this->app->singleton('command.app.up', function () {
            return new AppUpCommand;
        });
    }

    /**
     * 注册命令
     */
    protected function registerAppDownCommand()
    {
        $this->app->singleton('command.app.down', function () {
            return new AppDownCommand;
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
    protected function registerSessionTableCommand()
    {
        $this->app->singleton('command.session.table', function ($app) {
            return new SessionTableCommand($app['files'], $app['composer']);
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
}
