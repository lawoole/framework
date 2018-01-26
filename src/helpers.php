<?php

use Illuminate\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Carbon;
use Lawoole\Application;
use Symfony\Component\Debug\Exception\FatalThrowableError;

if (!function_exists('app')) {
    /**
     * 获得服务容器
     *
     * @param string $abstract
     * @param array $parameters
     *
     * @return \Lawoole\Application|mixed
     */
    function app($abstract = null, $parameters = [])
    {
        if ($abstract === null) {
            return Application::getInstance();
        }

        return Application::getInstance()->make($abstract, $parameters);
    }
}

if (!function_exists('base_path')) {
    /**
     * 获得项目基础路径
     *
     * @param string $path
     *
     * @return string
     */
    function base_path($path = '')
    {
        return app()->basePath($path);
    }
}

if (!function_exists('config')) {
    /**
     * 获得配置
     *
     * @param array|string $key
     * @param mixed $default
     *
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return resolve('config');
        }

        return resolve('config')->get($key, $default);
    }
}

if (!function_exists('database_path')) {
    /**
     * 获得数据库文件路径
     *
     * @param string $path
     *
     * @return string
     */
    function database_path($path = '')
    {
        return app()->databasePath($path);
    }
}

if (!function_exists('resolve')) {
    /**
     * 从容器中解析对象
     *
     * @param string $abstract
     * @param array $parameters
     *
     * @return mixed
     */
    function resolve($abstract, $parameters = [])
    {
        return Container::getInstance()->make($abstract, $parameters);
    }
}

if (!function_exists('resource_path')) {
    /**
     * 获得资源文件路径
     *
     * @param string $path
     *
     * @return string
     */
    function resource_path($path = '')
    {
        return app()->resourcePath($path);
    }
}

if (!function_exists('route_path')) {
    /**
     * 获得路由规则存储路径
     *
     * @param string $path
     *
     * @return string
     */
    function route_path($path = '')
    {
        return app()->routePath($path);
    }
}

if (!function_exists('schedule_path')) {
    /**
     * 获得定时任务存储路径
     *
     * @param string $path
     *
     * @return string
     */
    function schedule_path($path = '')
    {
        return app()->schedulePath($path);
    }
}

if (!function_exists('storage_path')) {
    /**
     * 获得本地存储文件目录
     *
     * @param string $path
     *
     * @return string
     */
    function storage_path($path = '')
    {
        return app()->storagePath($path);
    }
}

if (!function_exists('now')) {
    /**
     * 获得当前的时间操作对象
     *
     * @param \DateTimeZone|string $timezone
     *
     * @return \Carbon\Carbon
     */
    function now($timezone = null)
    {
        return Carbon::now($timezone);
    }
}

if (!function_exists('today')) {
    /**
     * 获得当日的时间操作对象
     *
     * @param \DateTimeZone|string $timezone
     *
     * @return \Carbon\Carbon
     */
    function today($timezone = null)
    {
        return Carbon::today($timezone);
    }
}

if (!function_exists('report')) {
    /**
     * 报告异常
     *
     * @param \Throwable $throwable
     */
    function report(Throwable $throwable)
    {
        if (!$throwable instanceof Exception) {
            $throwable = new FatalThrowableError($throwable);
        }

        app(ExceptionHandler::class)->report($throwable);
    }
}

if (!function_exists('view')) {
    /**
     * 获得视图
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    function view($view = null, $data = [], $mergeData = [])
    {
        $factory = app(ViewFactory::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}

if (!function_exists('info')) {
    /**
     * 记录日志
     *
     * @param string $message
     * @param array $context
     */
    function info($message, $context = [])
    {
        app('log')->info($message, $context);
    }
}

if (!function_exists('logger')) {
    /**
     * 记录日志
     *
     * @param string $message
     * @param array $context
     *
     * @return \Psr\Log\LoggerInterface|null
     */
    function logger($message = null, array $context = [])
    {
        if (is_null($message)) {
            return app('log');
        }

        return app('log')->debug($message, $context);
    }
}
