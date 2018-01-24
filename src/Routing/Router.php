<?php
namespace Lawoole\Routing;

use FastRoute\DataGenerator\GroupCountBased as DataGeneratorGroupCount;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as StdRouteParser;
use Illuminate\Support\Traits\Macroable;
use Lawoole\Contracts\Foundation\Application;

class Router
{
    use Macroable;

    /**
     * 所有支持的请求方法
     *
     * @var array
     */
    const VERBS = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * FastRoute 路由定义收集器
     *
     * @var \FastRoute\RouteCollector
     */
    protected $routeCollector;

    /**
     * 全局调用中间件
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * 路由调度中间件
     *
     * @var array
     */
    protected $routeMiddleware = [];

    /**
     * 路由定义
     *
     * @var array
     */
    protected $routes = [];

    /**
     * 命名路由
     *
     * @var array
     */
    public $namedRoutes = [];

    /**
     * 分组路由属性记录栈
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * 创建路由器实例
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        // 建立 FastRoute 路由定义收集器
        $this->routeCollector = new RouteCollector(new StdRouteParser, new DataGeneratorGroupCount);
    }

    /**
     * 获得服务容器
     *
     * @return \Lawoole\Contracts\Foundation\Application
     */
    public function app()
    {
        return $this->app;
    }

    /**
     * 解析路由动作
     *
     * @param mixed $action
     *
     * @return array
     */
    protected function parseAction($action)
    {
        if (is_string($action)) {
            return ['uses' => $action];
        } elseif (!is_array($action)) {
            return [$action];
        }

        // 解析中间件
        if (isset($action['middleware']) && is_string($action['middleware'])) {
            $action['middleware'] = explode('|', $action['middleware']);
        }

        return $action;
    }

    /**
     * 按组批量注册路由
     *
     * @param array $attributes
     * @param callable $callback
     */
    public function group(array $attributes, $callback)
    {
        // 解析中间件
        if (isset($attributes['middleware']) && is_string($attributes['middleware'])) {
            $attributes['middleware'] = explode('|', $attributes['middleware']);
        }

        // 将属性推入分组路由属性记录栈
        if ($this->groupStack) {
            $this->groupStack[] = $this->mergeGroupAttributes($attributes, end($this->groupStack));
        } else {
            $this->groupStack[] = $attributes;
        }

        call_user_func($callback, $this);

        // 分组路由属性出栈
        array_pop($this->groupStack);
    }

    /**
     * 合并分组路由属性
     *
     * @param array $incoming
     * @param array $existing
     *
     * @return array
     */
    public function mergeGroupAttributes(array $incoming, array $existing)
    {
        // 合并命名空间
        if (isset($incoming['namespace'])) {
            if (isset($existing['namespace']) && strpos($incoming['namespace'], '\\') !== 0) {
                $incoming['namespace'] = trim($existing['namespace'], '\\').'\\'.trim($incoming['namespace'], '\\');
            } else {
                $incoming['namespace'] = trim($incoming['namespace'], '\\');
            }
        } else {
            $incoming['namespace'] = $existing['namespace'] ?? null;
        }

        // 合并路径前缀
        if (isset($incoming['prefix'])) {
            if (isset($existing['prefix'])) {
                $incoming['prefix'] = trim($existing['prefix'], '/').'/'.trim($incoming['prefix'], '/');
            } else {
                $incoming['prefix'] = trim($incoming['prefix'], '/');
            }
        } else {
            $incoming['prefix'] = $existing['prefix'] ?? null;
        }

        // 合并路径后缀
        if (isset($existing['suffix']) && !isset($incoming['suffix'])) {
            $incoming['suffix'] = $existing['suffix'];
        }

        // 合并路由别名
        if (isset($existing['as'])) {
            if (isset($incoming['as'])) {
                $incoming['as'] = $existing['as'].'.'.$incoming['as'];
            } else {
                $incoming['as'] = $existing['as'];
            }
        }

        unset($existing['namespace'], $existing['prefix'], $existing['suffix'], $existing['as']);

        // 域名定义
        if (isset($incoming['domain'])) {
            unset($existing['domain']);
        }

        return array_merge_recursive($existing, $incoming);
    }

    /**
     * 注册路由
     *
     * @param array|string $methods
     * @param string $uri
     * @param mixed $action
     *
     * @return $this
     */
    public function register($methods, $uri, $action)
    {
        $action = $this->parseAction($action);

        $attributes = end($this->groupStack);

        if ($attributes) {
            if (isset($attributes['prefix'])) {
                $uri = trim($attributes['prefix'], '/').'/'.trim($uri, '/');
            }

            if (isset($attributes['suffix'])) {
                $uri = trim($uri, '/').rtrim($attributes['suffix'], '/');
            }

            $action = $this->mergeAttributes($action, $attributes);
        }

        $uri = '/'.trim($uri, '/');

        if (isset($action['as'])) {
            $this->namedRoutes[$action['as']] = $uri;
        }

        foreach ((array) $methods as $method) {
            $routeKey = $method.$uri;
            // 注册路由
            $this->routes[$routeKey] = ['method' => $method, 'uri' => $uri, 'action' => $action];
            $this->routeCollector->addRoute($method, $uri, $routeKey);
        }

        return $this;
    }

    /**
     * 合并参数
     *
     * @param array $action
     * @param array $attributes
     *
     * @return array
     */
    protected function mergeAttributes(array $action, array $attributes)
    {
        // 合并路由别名
        if (isset($attributes['as'])) {
            if (isset($action['as'])) {
                $action['as'] = $attributes['as'].'.'.$action['as'];
            } else {
                $action['as'] = $attributes['as'];
            }
        }

        // 合并中间件配置
        if (isset($attributes['middleware'])) {
            if (isset($action['middleware'])) {
                $action['middleware'] = array_merge($attributes['middleware'], $action['middleware']);
            } else {
                $action['middleware'] = $attributes['middleware'];
            }
        }

        // 合并控制器
        if (isset($attributes['namespace']) && isset($action['uses'])) {
            $action['uses'] = $attributes['namespace'].'\\'.$action['uses'];
        }

        return $action;
    }

    /**
     * 注册路由
     *
     * @param array|string $methods
     * @param string $uri
     * @param mixed $action
     *
     * @return $this
     */
    public function match($methods, $uri, $action)
    {
        return $this->register(array_map('strtoupper', (array) $methods), $uri, $action);
    }

    /**
     * 注册路由
     *
     * @param string $uri
     * @param mixed $action
     *
     * @return $this
     */
    public function get($uri, $action)
    {
        return $this->register('GET', $uri, $action);
    }

    /**
     * 注册路由
     *
     * @param string $uri
     * @param mixed $action
     *
     * @return $this
     */
    public function post($uri, $action)
    {
        return $this->register('POST', $uri, $action);
    }

    /**
     * 注册路由
     *
     * @param string $uri
     * @param mixed $action
     *
     * @return $this
     */
    public function put($uri, $action)
    {
        return $this->register('PUT', $uri, $action);
    }

    /**
     * 注册路由
     *
     * @param string $uri
     * @param mixed $action
     *
     * @return $this
     */
    public function patch($uri, $action)
    {
        return $this->register('PATCH', $uri, $action);
    }

    /**
     * 注册路由
     *
     * @param string $uri
     * @param mixed $action
     *
     * @return $this
     */
    public function delete($uri, $action)
    {
        return $this->register('DELETE', $uri, $action);
    }

    /**
     * 注册路由
     *
     * @param string $uri
     * @param mixed $action
     *
     * @return $this
     */
    public function options($uri, $action)
    {
        return $this->register('OPTIONS', $uri, $action);
    }

    /**
     * 注册路由
     *
     * @param string $uri
     * @param mixed $action
     *
     * @return $this
     */
    public function any($uri, $action = null)
    {
        return $this->register(self::VERBS, $uri, $action);
    }

    /**
     * 获得路由定义
     *
     * @param string $routeKey
     *
     * @return array
     */
    public function getRoute($routeKey)
    {
        return $this->routes[$routeKey] ?? [];
    }

    /**
     * 获得所有路由定义
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * 获得 FastRoute 路由定义收集器
     *
     * @return \FastRoute\RouteCollector
     */
    public function getRouteCollector()
    {
        return $this->routeCollector;
    }

    /**
     * 定义全局调度中间件
     *
     * @param mixed $middleware
     *
     * @return $this
     */
    public function middleware($middleware)
    {
        $middleware = (array) $middleware;

        $this->middleware = array_unique(array_merge($this->middleware, $middleware));

        return $this;
    }

    /**
     * 获得全局调度中间件集合
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * 定义路由调度中间件
     *
     * @param array $middleware
     *
     * @return $this
     */
    public function routeMiddleware(array $middleware)
    {
        $this->routeMiddleware = array_merge($this->routeMiddleware, $middleware);

        return $this;
    }

    /**
     * 获得路由调度中间件
     *
     * @return array
     */
    public function getRouteMiddleware()
    {
        return $this->routeMiddleware;
    }
}
