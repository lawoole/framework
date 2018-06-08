<?php
namespace Lawoole\Homer;

use Illuminate\Support\ServiceProvider;
use Lawoole\Homer\Serialization\Serializers\SerializerFactory;
use Lawoole\Homer\Transport\ClientFactory;
use Lawoole\Homer\Transport\Whisper\WhisperServerSocket;

class HomerServiceProvider extends ServiceProvider
{
    /**
     * 注册服务
     */
    public function register()
    {
        $this->registerContext();

        $this->registerDispatcher();

        $this->registerHomer();

        $this->registerClientFactory();

        $this->registerSerializerFactory();

        $this->registerServerSockets();
    }

    /**
     * 注册调用上下文
     */
    protected function registerContext()
    {
        $this->app->singleton('homer.context', function ($app) {
            return new Context($app);
        });
    }

    /**
     * 注册调用分发器
     */
    protected function registerDispatcher()
    {
        $this->app->singleton('homer.dispatcher', function () {
            return new Dispatcher;
        });
    }

    /**
     * 注册 Homer 管理器
     */
    protected function registerHomer()
    {
        $this->app->singleton('homer', function ($app) {
            return new HomerManager($app, $app['config']['homer']);
        });
    }

    /**
     * 注册客户端工厂
     */
    protected function registerClientFactory()
    {
        $this->app->singleton('homer.factory.client', function ($app) {
            return new ClientFactory($app);
        });
    }

    /**
     * 注册序列化工具工厂
     */
    protected function registerSerializerFactory()
    {
        $this->app->singleton('homer.factory.serializer', SerializerFactory::class);
    }

    /**
     * 注册服务 Socket
     */
    protected function registerServerSockets()
    {
        $this->app->instance('server.sockets.whisper', WhisperServerSocket::class);
    }

    /**
     * 启动服务
     */
    public function boot()
    {
        $this->app->make('homer')->boot($this->app);
    }
}
