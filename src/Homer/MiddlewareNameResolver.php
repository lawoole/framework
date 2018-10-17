<?php
namespace Lawoole\Homer;

use Closure;

class MiddlewareNameResolver
{
    /**
     * Resolve the middleware name to a class name(s) preserving passed parameters.
     *
     * @param string $name
     * @param array $nameMiddleware
     * @param array $middlewareGroups
     *
     * @return \Closure|string|array
     */
    public static function resolve($name, $nameMiddleware, $middlewareGroups)
    {
        if ($name instanceof Closure) {
            return $name;
        }

        if (isset($nameMiddleware[$name]) && $nameMiddleware[$name] instanceof Closure) {
            return $nameMiddleware[$name];
        }

        if (isset($middlewareGroups[$name])) {
            return static::parseMiddlewareGroup($name, $nameMiddleware, $middlewareGroups);
        }

        [$middleware, $parameters] = array_pad(explode(':', $name, 2), 2, null);

        if (isset($nameMiddleware[$middleware])) {
            $middleware = $nameMiddleware[$middleware];
        }

        return $middleware.($parameters ? ':'.$parameters : '');
    }

    /**
     * Parse the middleware group and format it for usage.
     *
     * @param string $name
     * @param array $nameMiddleware
     * @param array $middlewareGroups
     *
     * @return array
     */
    protected static function parseMiddlewareGroup($name, $nameMiddleware, $middlewareGroups)
    {
        $results = [];

        foreach ($middlewareGroups[$name] as $middleware) {
            if (isset($middlewareGroups[$middleware])) {
                $results = array_merge($results, static::parseMiddlewareGroup(
                    $middleware, $nameMiddleware, $middlewareGroups
                ));

                continue;
            }

            [$middleware, $parameters] = array_pad(explode(':', $middleware, 2), 2, null);

            if (isset($nameMiddleware[$middleware])) {
                $middleware = $nameMiddleware[$middleware];
            }

            $results[] = $middleware.($parameters ? ':'.$parameters : '');
        }

        return $results;
    }
}