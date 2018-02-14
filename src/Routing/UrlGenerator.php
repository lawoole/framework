<?php
namespace Lawoole\Routing;

use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Lawoole\Application;

class UrlGenerator implements UrlGeneratorContract
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Application
     */
    protected $app;

    /**
     * 路由
     *
     * @var \Lawoole\Routing\Router
     */
    protected $router;

    /**
     * 命名路由映射
     *
     * @var array
     */
    protected $namedRoutes;

    /**
     * 根命名空间
     *
     * @var string
     */
    protected $rootNamespace;

    /**
     * 控制器路由映射
     *
     * @var array
     */
    protected $actionRoutes;

    /**
     * 创建 Url 生成器
     *
     * @param \Lawoole\Application $app
     * @param \Lawoole\Routing\Router $router
     */
    public function __construct(Application $app, Router $router)
    {
        $this->app = $app;

        $this->setRouter($router);
    }

    /**
     * 设置控制器根命名空间
     *
     * @param string $rootNamespace
     *
     * @return $this
     */
    public function setRootControllerNamespace($rootNamespace)
    {
        $this->rootNamespace = trim($rootNamespace, '\\');

        return $this;
    }

    /**
     * 获得控制器根命名空间
     *
     * @return string
     */
    public function getRootControllerNamespace()
    {
        return $this->rootNamespace;
    }

    /**
     * 设置路由
     *
     * @param \Lawoole\Routing\Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        $this->refreshNameLookups();
        $this->refreshActionLookups();
    }

    /**
     * 更新命名路由查找
     */
    public function refreshNameLookups()
    {
        $routes = $this->router->getRoutes();

        $this->namedRoutes = [];

        foreach ($routes as $route) {
            $action = $route['action'];

            if (isset($action['as'])) {
                $this->namedRoutes[$action['as']] = $route['uri'];
            }
        }
    }

    /**
     * 更新控制器路由查找
     */
    public function refreshActionLookups()
    {
        $routes = $this->router->getRoutes();

        $this->actionRoutes = [];

        foreach ($routes as $route) {
            $action = $route['action'];

            if (isset($action['uses'])) {
                $this->actionRoutes[$action['uses']] = $route['uri'];
            }
        }
    }

    /**
     * 获得当前请求的完整 Url
     *
     * @return string
     */
    public function full()
    {
        return $this->app->make('request')->fullUrl();
    }

    /**
     * 获得当前请求的 Url
     *
     * @return string
     */
    public function current()
    {
        $pathInfo = $this->app->make('request')->getPathInfo();

        return $this->to($pathInfo);
    }

    /**
     * 生成 Url
     *
     * @param string $path
     * @param array $extra
     * @param bool|null $secure
     *
     * @return string
     */
    public function to($path, $extra = [], $secure = null)
    {
        // 检查 Url 是否已经是合理
        if ($this->isValidUrl($path)) {
            return $path;
        }

        $root = $this->formatRoot($this->formatScheme($secure));

        $extra = $this->formatParameters($extra);

        $tail = implode('/', array_map('rawurlencode', (array) $extra));

        return $this->format($root, $path, $tail);
    }

    /**
     * 生成安全 Url
     *
     * @param string $path
     * @param array $parameters
     *
     * @return string
     */
    public function secure($path, $parameters = [])
    {
        return $this->to($path, $parameters, true);
    }

    /**
     * 生成资源 Url
     *
     * @param string $path
     * @param bool|null $secure
     *
     * @return string
     */
    public function asset($path, $secure = null)
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }

        $root = $this->formatRoot($this->formatScheme($secure));

        return $this->removeIndex($root).'/'.trim($path, '/');
    }

    /**
     * 生成安全资源 Url
     *
     * @param string $path
     *
     * @return string
     */
    public function secureAsset($path)
    {
        return $this->asset($path, true);
    }

    /**
     * 将 index.php 从路径中移除
     *
     * @param string $root
     *
     * @return string
     */
    protected function removeIndex($root)
    {
        return str_replace('/index.php', '', $root);
    }

    /**
     * 根据命名路由生成 Url
     *
     * @param string $name
     * @param mixed $parameters
     * @param bool|null $secure
     *
     * @return string
     */
    public function route($name, $parameters = [], $secure = null)
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new InvalidArgumentException("Route [{$name}] not defined");
        }

        return $this->formatRouteUri($this->namedRoutes[$name], $parameters, $secure);
    }

    /**
     * 获得控制器对应的 Url
     *
     * @param string $action
     * @param mixed $parameters
     * @param bool|null $secure
     *
     * @return string
     */
    public function action($action, $parameters = [], $secure = null)
    {
        $controllerAction = $this->formatAction($action);

        if (!isset($this->actionRoutes[$controllerAction])) {
            throw new InvalidArgumentException("Action [{$action}] not defined");
        }

        return $this->formatRouteUri($this->actionRoutes[$controllerAction], $parameters, $secure);
    }

    /**
     * 格式化控制器
     *
     * @param string $action
     *
     * @return string
     */
    protected function formatAction($action)
    {
        if ($this->rootNamespace && strpos($action, '\\') !== 0) {
            return $this->rootNamespace.'\\'.$action;
        }

        return trim($action, '\\');
    }

    /**
     * 格式化路由 Uri
     *
     * @param string $uri
     * @param mixed $parameters
     * @param bool|null $secure
     *
     * @return string
     */
    protected function formatRouteUri($uri, $parameters, $secure)
    {
        $parameters = $this->formatParameters($parameters);

        $uri = preg_replace_callback('/\{(.*?)(:.*?)?(\{[0-9,]+\})?\}/', function ($m) use (&$parameters) {
            return isset($parameters[$m[1]]) ? array_pull($parameters, $m[1]) : $m[0];
        }, $uri);

        $uri = $this->to($uri, [], $secure);

        if ($parameters) {
            $uri .= '?'.http_build_query($parameters);
        }

        return $uri;
    }

    /**
     * 判断 Url 是否合法
     *
     * @param string $path
     *
     * @return bool
     */
    public function isValidUrl($path)
    {
        if (starts_with($path, ['#', '//', 'mailto:', 'tel:', 'http://', 'https://'])) {
            return true;
        }

        return filter_var($path, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * 格式化 Url
     *
     * @param string $root
     * @param string $path
     * @param string $tail
     *
     * @return string
     */
    protected function format($root, $path, $tail = '')
    {
        return trim($root.'/'.trim($path.'/'.$tail, '/'), '/');
    }

    /**
     * 格式化协议
     *
     * @param bool|null $secure
     *
     * @return string
     */
    public function formatScheme($secure)
    {
        if ($secure === null) {
            $secure = $this->app->make('request')->secure();
        }

        return $secure ? 'https://' : 'http://';
    }

    /**
     * 格式化根路径
     *
     * @param string $scheme
     * @param string $root
     *
     * @return string
     */
    public function formatRoot($scheme, $root = null)
    {
        if ($root === null) {
            $root = $this->app->make('request')->root();
        }

        $start = Str::startsWith($root, 'http://') ? 'http://' : 'https://';

        return preg_replace('~'.$start.'~', $scheme, $root, 1);
    }

    /**
     * 格式化参数
     *
     * @param mixed|array $parameters
     *
     * @return array
     */
    protected function formatParameters($parameters)
    {
        $parameters = is_array($parameters) ? $parameters : [$parameters];

        foreach ($parameters as $key => $parameter) {
            if ($parameter instanceof UrlRoutable) {
                $parameters[$key] = $parameter->getRouteKey();
            }
        }

        return $parameters;
    }
}
