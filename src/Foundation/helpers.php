<?php

if (! function_exists('route_path')) {
    /**
     * Get the path to the route files.
     *
     * @param string $path
     *
     * @return string
     */
    function route_path($path = '')
    {
        return app()->make('path.route').($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}