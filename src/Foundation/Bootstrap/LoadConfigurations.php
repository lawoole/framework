<?php
namespace Lawoole\Foundation\Bootstrap;

use Closure;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration as BaseLoadConfigurations;
use Symfony\Component\Finder\Finder;

class LoadConfigurations extends BaseLoadConfigurations
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(Application $app)
    {
        // We will spin through all of the configuration files in the configuration
        // directory and load each one into the repository. This will make all of the
        // options available to the developer for use in various parts of this app.
        $app->instance('config', $config = new Repository);

        $this->loadConfigurationFiles($app, $config);

        $this->loadEnvironmentConfigurations($app, $config);

        // Next, we will set the application's environment based on the configuration
        // values that were loaded. We will pass a callback which will be used to get
        // the environment in a web context where an "--env" switch is not present.
        $app->detectEnvironment(function () use ($config) {
            return $config->get('app.env', 'production');
        });

        date_default_timezone_set($config->get('app.timezone', 'UTC'));

        mb_internal_encoding('UTF-8');
    }

    /**
     * Load the environment configuration items.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Illuminate\Contracts\Config\Repository $repository
     */
    public function loadEnvironmentConfigurations($app, RepositoryContract $repository)
    {
        $configPath = realpath($app->environmentPath());

        $files = $this->getConfigurationFilesInPath($configPath);

        foreach ($files as $scope => $path) {
            $items = require $path;

            foreach ($items as $key => $value) {
                $key = "{$scope}.{$key}";

                // We can use a Closure as the value, and it will be called with
                // the default configuration item. It useful for replace configurations
                // defined as array or other complex form.
                if ($value instanceof Closure) {
                    $value = $value($repository->get($key), $app);
                }

                // We can specify a deep-level configuration item using the dot in key,
                // and just to change the item.
                $repository->set($key, $value);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigurationFiles(Application $app)
    {
        $configPath = realpath($app->configPath());

        return $this->getConfigurationFilesInPath($configPath);
    }

    /**
     * Get all of the configuration files in the given directory.
     *
     * @param $configPath
     *
     * @return array
     */
    protected function getConfigurationFilesInPath($configPath)
    {
        $files = [];

        if (!is_dir($configPath)) {
            return $files;
        }

        foreach (Finder::create()->files()->name('*.php')->depth(0)->in($configPath) as $file) {
            $files[$file->getBasename('.php')] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }
}
