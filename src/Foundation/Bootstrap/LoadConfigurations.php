<?php
namespace Lawoole\Foundation\Bootstrap;

use Closure;
use Illuminate\Config\Repository;
use Lawoole\Contracts\Foundation\Application;
use Symfony\Component\Finder\Finder;

class LoadConfigurations
{
    /**
     * 加载配置文件
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     */
    public function bootstrap(Application $app)
    {
        // 实例化配置对象
        $app->instance('config', $repository = new Repository);

        // 载入配置
        $this->loadConfigurations($app, $repository);

        // 载入环境配置文件
        $this->loadEnvironmentConfigurations($app, $repository);

        // 根据配置设置时区
        date_default_timezone_set($repository->get('app.timezone', 'PRC'));

        mb_internal_encoding('UTF-8');
    }

    /**
     * 载入配置文件
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     * @param \Illuminate\Config\Repository $repository
     */
    public function loadConfigurations($app, Repository $repository)
    {
        $files = $this->getConfigurationFiles($app->configPath());

        foreach ($files as $scope => $path) {
            $repository->set($scope, require $path);
        }
    }

    /**
     * 载入环境配置文件
     *
     * @param \Lawoole\Application $app
     * @param \Illuminate\Config\Repository $repository
     */
    public function loadEnvironmentConfigurations($app, Repository $repository)
    {
        $files = $this->getConfigurationFiles(
            $repository->get('config.environment', $app->configPath('environment'))
        );

        foreach ($files as $scope => $path) {
            $items = require $path;

            foreach ($items as $key => $value) {
                $key = "{$scope}.{$key}";

                // 支持闭包形式定义环境配置，更好的实现配置的灵活性
                if ($value instanceof Closure) {
                    $value = $value($repository->get($key), $app);
                }

                // 可以根据环境配置中键名的改变，替换指定深度的配置
                $repository->set($key, $value);
            }
        }
    }

    /**
     * 获得指定目录中配置文件的集合
     *
     * @param string $configPath
     *
     * @return array
     */
    protected function getConfigurationFiles($configPath)
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
