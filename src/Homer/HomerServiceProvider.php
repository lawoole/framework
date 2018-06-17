<?php
namespace Lawoole\Homer;

use Illuminate\Support\ServiceProvider;
use Lawoole\Homer\Serialize\Factory as SerializerFactory;
use Lawoole\Homer\Transport\ClientFactory;
use Lawoole\Homer\Transport\Whisper\WhisperServerSocket;

class HomerServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerContext();

        $this->registerDispatcher();

        $this->registerServerSockets();

        $this->registerSerializerFactory();

        $this->registerClientFactory();

        $this->registerHomer();
    }

    /**
     * Register the invoking context instance.
     */
    protected function registerContext()
    {
        $this->app->singleton('homer.context', function ($app) {
            return new Context($app);
        });
    }

    /**
     * Register the invocation dispatcher instance.
     */
    protected function registerDispatcher()
    {
        $this->app->singleton('homer.dispatcher', function () {
            return new Dispatcher;
        });
    }

    /**
     * Register all extension socket protocols.
     */
    protected function registerServerSockets()
    {
        $this->app->instance('server.sockets.whisper', WhisperServerSocket::class);
    }

    /**
     * Register the serializer factory instance.
     */
    protected function registerSerializerFactory()
    {
        $this->app->singleton('homer.factory.serializer', function () {
            return new SerializerFactory;
        });

        $this->app->alias('homer.factory.serializer', SerializerFactory::class);
    }

    /**
     * Register the client factory instance.
     */
    protected function registerClientFactory()
    {
        $this->app->singleton('homer.factory.client', function ($app) {
            return new ClientFactory($app, $app['homer.factory.serializer']);
        });

        $this->app->alias('homer.factory.client', ClientFactory::class);
    }

    /**
     * Register the Homer manager.
     */
    protected function registerHomer()
    {
        $this->app->singleton('homer', function ($app) {
            return new HomerManager($app, $app['homer.context'], $app['homer.dispatcher'],
                $app['homer.factory.client'], $app['config']['homer']);
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
