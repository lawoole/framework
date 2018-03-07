<?php
namespace Lawoole\Foundation\Providers;

use Illuminate\Cache\Console\CacheTableCommand;
use Illuminate\Cache\Console\ClearCommand as CacheClearCommand;
use Illuminate\Cache\Console\ForgetCommand as CacheForgetCommand;
use Illuminate\Console\Application as ConsoleApplication;
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
use Illuminate\Queue\Console\FailedTableCommand;
use Illuminate\Queue\Console\FlushFailedCommand as FlushFailedQueueCommand;
use Illuminate\Queue\Console\ForgetFailedCommand as ForgetFailedQueueCommand;
use Illuminate\Queue\Console\ListenCommand as QueueListenCommand;
use Illuminate\Queue\Console\ListFailedCommand as ListFailedQueueCommand;
use Illuminate\Queue\Console\RestartCommand as QueueRestartCommand;
use Illuminate\Queue\Console\RetryCommand as QueueRetryCommand;
use Illuminate\Queue\Console\TableCommand;
use Illuminate\Queue\Console\WorkCommand as QueueWorkCommand;
use Illuminate\Support\ServiceProvider;
use Lawoole\Foundation\Console\DownCommand;
use Lawoole\Foundation\Console\KeyGenerateCommand;
use Lawoole\Foundation\Console\UpCommand;
use Lawoole\Foundation\Console\VersionCommand;
use Lawoole\Foundation\Console\ViewClearCommand;

class ArtisanServiceProvider extends ServiceProvider
{
    /**
     * 支持的命令
     *
     * @var array
     */
    protected $commands = [
        'Up'             => 'command.up',
        'Down'           => 'command.down',
        'Version'        => 'command.version',
        'QueueFlush'     => 'command.queue.flush',
        'QueueForget'    => 'command.queue.forget',
        'QueueListen'    => 'command.queue.listen',
        'QueueRestart'   => 'command.queue.restart',
        'QueueRetry'     => 'command.queue.retry',
        'QueueWork'      => 'command.queue.work',
        'ScheduleRun'    => 'command.schedule.run',
        'ScheduleFinish' => 'command.schedule.finish',
    ];

    /**
     * Artisan 命令
     *
     * @var array
     */
    protected $artisanCommands = [
        'CacheClear'       => 'command.cache.clear',
        'CacheForget'      => 'command.cache.forget',
        'CacheTable'       => 'command.cache.table',
        'FactoryMake'      => 'command.factory.make',
        'KeyGenerate'      => 'command.key.generate',
        'Migrate'          => 'command.migrate',
        'MigrateFresh'     => 'command.migrate.fresh',
        'MigrateInstall'   => 'command.migrate.install',
        'MigrateMake'      => 'command.migrate.make',
        'MigrateRefresh'   => 'command.migrate.refresh',
        'MigrateReset'     => 'command.migrate.reset',
        'MigrateRollback'  => 'command.migrate.rollback',
        'MigrateStatus'    => 'command.migrate.status',
        'QueueFailed'      => 'command.queue.failed',
        'QueueFailedTable' => 'command.queue.failed-table',
        'QueueTable'       => 'command.queue.table',
        'Seed'             => 'command.seed',
        'SeederMake'       => 'command.seeder.make',
        'ViewClear'        => 'command.view.clear',
    ];

    /**
     * 注册服务
     */
    public function register()
    {
        $commands = $this->commands;

        if (trim(ConsoleApplication::artisanBinary(), "'") == 'artisan') {
            $commands += $this->artisanCommands;
        }

        $this->registerCommands($commands);

        // 自定义命令
        if ($customCommands = $this->app['config']['console.commands']) {
            $this->commands($customCommands);
        }
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
    protected function registerVersionCommand()
    {
        $this->app->singleton('command.version', function () {
            return new VersionCommand;
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
    protected function registerKeyGenerateCommand()
    {
        $this->app->singleton('command.key.generate', function ($app) {
            return new KeyGenerateCommand;
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
    protected function registerQueueFailedCommand()
    {
        $this->app->singleton('command.queue.failed', function () {
            return new ListFailedQueueCommand;
        });
    }

    /**
     * 注册命令
     */
    protected function registerQueueFailedTableCommand()
    {
        $this->app->singleton('command.queue.failed-table', function ($app) {
            return new FailedTableCommand($app['files'], $app['composer']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerQueueFlushCommand()
    {
        $this->app->singleton('command.queue.flush', function () {
            return new FlushFailedQueueCommand;
        });
    }

    /**
     * 注册命令
     */
    protected function registerQueueForgetCommand()
    {
        $this->app->singleton('command.queue.forget', function () {
            return new ForgetFailedQueueCommand;
        });
    }

    /**
     * 注册命令
     */
    protected function registerQueueListenCommand()
    {
        $this->app->singleton('command.queue.listen', function ($app) {
            return new QueueListenCommand($app['queue.listener']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerQueueRestartCommand()
    {
        $this->app->singleton('command.queue.restart', function () {
            return new QueueRestartCommand;
        });
    }

    /**
     * 注册命令
     */
    protected function registerQueueRetryCommand()
    {
        $this->app->singleton('command.queue.retry', function () {
            return new QueueRetryCommand;
        });
    }

    /**
     * 注册命令
     */
    protected function registerQueueTableCommand()
    {
        $this->app->singleton('command.queue.table', function ($app) {
            return new TableCommand($app['files'], $app['composer']);
        });
    }

    /**
     * 注册命令
     */
    protected function registerQueueWorkCommand()
    {
        $this->app->singleton('command.queue.work', function ($app) {
            return new QueueWorkCommand($app['queue.worker']);
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
}
