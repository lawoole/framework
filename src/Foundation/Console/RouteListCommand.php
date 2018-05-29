<?php
namespace Lawoole\Foundation\Console;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class RouteListCommand extends Command
{
    /**
     * 命令名
     *
     * @var string
     */
    protected $name = 'route:list';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'List all registered routes';

    /**
     * 路由
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * 路由收集器
     *
     * @var \Illuminate\Routing\RouteCollection
     */
    protected $routes;

    /**
     * 路由列表标题
     *
     * @var array
     */
    protected $headers = ['Domain', 'Method', 'URI', 'Name', 'Action', 'Middleware'];

    /**
     * 创建路由列表命令
     *
     * @param \Illuminate\Routing\Router $router
     */
    public function __construct(Router $router)
    {
        parent::__construct();

        $this->router = $router;
        $this->routes = $router->getRoutes();
    }

    /**
     * 执行命令
     */
    public function handle()
    {
        if (count($this->routes) == 0) {
            $this->error('Your application doesn\'t have any routes.');

            return;
        }

        $this->displayRoutes($this->getRoutes());
    }

    /**
     * 预处理并获得路由
     *
     * @return array
     */
    protected function getRoutes()
    {
        $routes = collect($this->routes)->map(function ($route) {
            return $this->getRouteInformation($route);
        })->all();

        if ($sort = $this->option('sort')) {
            $routes = $this->sortRoutes($sort, $routes);
        }

        if ($this->option('reverse')) {
            $routes = array_reverse($routes);
        }

        return array_filter($routes);
    }

    /**
     * 获得给定的路由信息
     *
     * @param \Illuminate\Routing\Route $route
     *
     * @return array
     */
    protected function getRouteInformation(Route $route)
    {
        return $this->filterRoute([
            'host'       => $route->domain(),
            'method'     => implode('|', $route->methods()),
            'uri'        => $route->uri(),
            'name'       => $route->getName(),
            'action'     => ltrim($route->getActionName(), '\\'),
            'middleware' => $this->getMiddleware($route),
        ]);
    }

    /**
     * 根据字段字段对路由进行排序
     *
     * @param string $sort
     * @param array $routes
     *
     * @return array
     */
    protected function sortRoutes($sort, $routes)
    {
        return Arr::sort($routes, function ($route) use ($sort) {
            return $route[$sort];
        });
    }

    /**
     * 打印路由信息
     *
     * @param array $routes
     */
    protected function displayRoutes(array $routes)
    {
        $this->table($this->headers, $routes);
    }

    /**
     * 获得路由中间件
     *
     * @param \Illuminate\Routing\Route $route
     *
     * @return string
     */
    protected function getMiddleware($route)
    {
        return collect($route->gatherMiddleware())->map(function ($middleware) {
            return $middleware instanceof Closure ? 'Closure' : $middleware;
        })->implode(',');
    }

    /**
     * 过滤路由
     *
     * @param array $route
     *
     * @return array|null
     */
    protected function filterRoute(array $route)
    {
        if (($name = $this->option('name')) && !Str::contains($route['name'], $name)) {
            return null;
        }

        if (($path = $this->option('path')) && !Str::contains($route['uri'], $path)) {
            return null;
        }

        if (($method = $this->option('method')) && !Str::contains($route['method'], $method)) {
            return null;
        }

        return $route;
    }

    /**
     * 获得命令参数
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['method', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by method.'],
            ['name', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by name.'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by path.'],
            ['reverse', 'r', InputOption::VALUE_NONE, 'Reverse the ordering of the routes.'],
            ['sort', null, InputOption::VALUE_OPTIONAL,
                'The column (host, method, uri, name, action, middleware) to sort by.', 'uri'],
        ];
    }
}
