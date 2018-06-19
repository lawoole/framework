<?php
namespace Lawoole\Foundation;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Foundation\Application as BaseApplication;

class Application extends BaseApplication implements ApplicationContract
{
    /**
     * The Lawoole framework version.
     *
     * @var string
     */
    const VERSION = '0.5.0';

    /**
     * The name of service.
     *
     * @var string
     */
    protected $name = 'The Lawoole Service';

    /**
     * Get the name of application.
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Set the name of application.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
    protected function bindPathsInContainer()
    {
        parent::bindPathsInContainer();

        $this->instance('path.route', $this->routePath());
    }

    /**
     * {@inheritdoc}
     */
    public function environmentPath()
    {
        return $this->environmentPath ?: $this->configPath('environment');
    }

    /**
     * Get the path to the route files.
     *
     * @return string
     */
    public function routePath()
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'routes';
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
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function routesAreCached()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function registerCoreContainerAliases()
    {
        parent::registerCoreContainerAliases();

        foreach ([
             'app'                  => [\Lawoole\Foundation\Application::class],
             'console.input'        => [\Symfony\Component\Console\Input\InputInterface::class],
             'console.output'       => [\Symfony\Component\Console\Output\OutputInterface::class],
             'console.output.style' => [\Illuminate\Console\OutputStyle::class, \Lawoole\Console\OutputStyle::class],
             'homer'                => [\Lawoole\Contracts\Homer\Homer::class, \Lawoole\Homer\HomerManager::class],
             'homer.context'        => [\Lawoole\Contracts\Homer\Context::class, \Lawoole\Homer\Context::class],
             'homer.dispatcher'     => [\Lawoole\Homer\Calling\Dispatcher::class],
             'respondent'           => [\Lawoole\Http\Respondent::class],
             'schedule'             => [\Illuminate\Console\Scheduling\Schedule::class],
             'server'               => [\Lawoole\Contracts\Server\Server::class, \Lawoole\Server\Server::class],
             'server.swoole'        => [\Swoole\Server::class],
             'snowflake'            => [\Lawoole\Contracts\Snowflake\Snowflake::class, \Lawoole\Snowflake\Snowflake::class],
         ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }
}
