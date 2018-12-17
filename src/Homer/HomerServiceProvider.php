<?php
namespace Lawoole\Homer;

use Illuminate\Support\ServiceProvider;
use Lawoole\Contracts\Server\Factory as ServerFactory;
use Lawoole\Homer\Calling\Dispatcher;
use Lawoole\Homer\Transport\Whisper\WhisperServerSocket;

class HomerServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerServerSockets();

        $this->registerContext();

        $this->registerDispatcher();

        $this->registerHomer();
    }

    /**
     * Register all extension socket protocols.
     */
    protected function registerServerSockets()
    {
        $this->app->afterResolving(ServerFactory::class, function ($factory) {
            $factory->extendServerSocket('whisper', function ($app, $config) {
                return new WhisperServerSocket($app, $config);
            });
        });
    }

    /**
     * Register invoking context.
     */
    protected function registerContext()
    {
        $this->app->singleton('homer.context', function ($app) {
            return new Context($app);
        });
    }

    /**
     * Register invoking dispatcher.
     */
    protected function registerDispatcher()
    {
        $this->app->singleton('homer.dispatcher', function () {
            return new Dispatcher;
        });
    }

    /**
     * Register the Homer manager.
     */
    protected function registerHomer()
    {
        $this->app->singleton('homer', function ($app) {
            return new HomerManager($app, $app['homer.context'], $app['homer.dispatcher'], $app['config']['homer']);
        });
    }

    /**
     * Bootstrap the Homer service.
     */
    public function boot()
    {
        $this->app->make('homer')->boot($this->app);
    }
}
