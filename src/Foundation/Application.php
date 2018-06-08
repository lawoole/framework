<?php
namespace Lawoole\Foundation;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Foundation\Application as BaseApplication;

class Application extends BaseApplication implements ApplicationContract
{
    /**
     * The name of service.
     *
     * @var string
     */
    const NAME = 'The Lawoole Service';

    /**
     * The Lawoole framework version.
     *
     * @var string
     */
    const VERSION = '0.5.0';

    /**
     * Get the name of service.
     *
     * @return string
     */
    public function name()
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function version()
    {
        return static::VERSION.' (Laravel Components 5.6.*)';
    }

    /**
     * {@inheritdoc}
     */
    public function registerConfiguredProviders()
    {
        $providers = $this['config']['app.providers'];

        // Since the service is started, the request can be processed repeatedly, and
        // we can ignore the consumption of the application startup, so that we do not
        // need to do delay registration and cache compilation.
        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configurationIsCached()
    {
        // Configurations will never be cached.
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function routesAreCached()
    {
        // Routes will never be cached.
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function registerCoreContainerAliases()
    {
        parent::registerCoreContainerAliases();

        foreach ([
             'app'              => [\Lawoole\Foundation\Application::class],
             'console.input'    => [\Symfony\Component\Console\Input\InputInterface::class],
             'console.output'   => [\Symfony\Component\Console\Output\OutputInterface::class],
             'homer'            => [\Lawoole\Contracts\Rpc\Homer::class, \Lawoole\Homer\HomerManager::class],
             'homer.context'    => [\Lawoole\Homer\Context::class],
             'homer.dispatcher' => [\Lawoole\Homer\Dispatcher::class],
             'http.manager'     => [\Lawoole\Homer\Dispatcher::class],
             'schedule'         => [\Illuminate\Console\Scheduling\Schedule::class],
             'server'           => [\Lawoole\Contracts\Server\Server::class, \Lawoole\Server\ServerManager::class],
             'server.swoole'    => [\Swoole\Server::class],
        ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }

}