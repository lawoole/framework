<?php
namespace Lawoole\Http;

use Closure;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->setRootControllerNamespace();

        $this->loadRoutes();

        $this->app->booted(function () {
            $this->refreshRoutes($this->app['router']);
        });
    }

    /**
     * Refresh the router's lookups.
     *
     * @param \Illuminate\Routing\Router $router
     */
    protected function refreshRoutes($router)
    {
        $router->getRoutes()->refreshNameLookups();
        $router->getRoutes()->refreshActionLookups();
    }

    /**
     * Load the routes.
     */
    protected function loadRoutes()
    {
        if (($routes = $this->app['config']['http.routes']) == null) {
            return;
        }

        $router = $this->app['router'];

        $namespace = $this->getNamespace();

        foreach ($routes as $route) {
            if ($route instanceof Closure) {
                // We can defined routes with a Closure.
                call_user_func($route, $router);

                continue;
            }

            $attributes = $this->normalizeRouteAttributes($route);

            $filePath = array_pull($attributes, 'path');

            if ($namespace) {
                $attributes['namespace'] = $this->getGroupRouteNamespace($namespace, $attributes);
            }

            if (file_exists($filePath)) {
                $router->group($attributes, function ($router) use ($filePath) {
                    include $filePath;
                });
            }
        }
    }

    /**
     * Normalize the route attributes.
     *
     * @param array|string $route
     *
     * @return array
     */
    protected function normalizeRouteAttributes($route)
    {
        if (is_string($route)) {
            return ['path' => $route];
        }

        return $route;
    }

    /**
     * Get the route group's namespace.
     *
     * @param string $namespace
     * @param array $attributes
     *
     * @return string
     */
    protected function getGroupRouteNamespace($namespace, $attributes)
    {
        if (! isset($attributes['namespace'])) {
            return $namespace;
        } elseif (strpos($attributes['namespace'], '\\') !== 0) {
            return $namespace.'\\'.$attributes['namespace'];
        }

        return $attributes['namespace'];
    }

    /**
     * Get the root controllers' root namespace.
     *
     * @return string
     */
    protected function getNamespace()
    {
        return $this->app['config']['http.namespace'];
    }

    /**
     * Set the root controller namespace for the application.
     */
    protected function setRootControllerNamespace()
    {
        if ($namespace = $this->getNamespace()) {
            return $this->app[UrlGenerator::class]->setRootControllerNamespace($namespace);
        }
    }
}
