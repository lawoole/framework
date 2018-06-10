<?php
namespace Lawoole\Foundation\Support\Providers;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as BaseServiceProvider;

class RouteServiceProvider extends BaseServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected function loadRoutes()
    {
        parent::loadRoutes();

        if (($routes = $this->app['config']['http.routes']) == null) {
            return;
        }

        $namespace = $this->app['config']['http.namespace'];

        foreach ($routes as $definition) {
            if ($definition instanceof Closure) {
                // 如果是个闭包，直接执行
                call_user_func($definition, $router);

                continue;
            }

            if (is_string($definition)) {
                $definition = [
                    'path' => $definition
                ];
            }

            $path = array_pull($definition, 'path');

            if ($namespace && !isset($definition['namespace'])) {
                $definition['namespace'] = $namespace;
            }

            if (file_exists($path)) {
                $router->group($definition, function ($router) use ($path) {
                    include $path;
                });
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setRootControllerNamespace()
    {
        if ($this->namespace !== null) {
            return $this->app[UrlGenerator::class]->setRootControllerNamespace($this->namespace);
        }

        // We can set the root controller namespace in the configuration by 'http.namespace'.
        // So that, we can configure the route without overwrite this ServiceProvider.
        if ($namespace = $this->app['config']['http.namespace']) {
            return $this->app[UrlGenerator::class]->setRootControllerNamespace($namespace);
        }
    }
}
