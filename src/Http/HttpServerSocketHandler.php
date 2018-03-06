<?php
namespace Lawoole\Http;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Routing\Pipeline;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use Lawoole\Contracts\Foundation\Application;
use Lawoole\Server\ServerSockets\HttpServerSocketHandler as BaseHttpServerSocketHandler;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class HttpServerSocketHandler extends BaseHttpServerSocketHandler
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * 控制台共用请求
     *
     * @var \Illuminate\Http\Request
     */
    protected $consoleRequest;

    /**
     * 路由器
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * 全局中间件
     *
     * @var array
     */
    protected $middleware;

    /**
     * 中间件优先级定义
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Auth\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];

    /**
     * 创建 Http 服务事件处理器
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     * @param \Illuminate\Routing\Router $router
     */
    public function __construct(Application $app, Router $router)
    {
        $this->app = $app;
        $this->router = $router;
        $this->consoleRequest = $app['console.request'];

        $this->loadMiddleware();
    }

    /**
     * 载入中间件配置
     */
    protected function loadMiddleware()
    {
        $config = $this->app->make('config');

        $this->router->middlewarePriority = $this->middlewarePriority;

        $this->middleware = $config->get('http.middleware', []);

        $middlewareGroups = $config->get('http.middleware_groups', []);

        foreach ($middlewareGroups as $key => $middleware) {
            $this->router->middlewareGroup($key, $middleware);
        }

        $routeMiddleware = $config->get('http.route_middleware', []);

        foreach ($routeMiddleware as $key => $middleware) {
            $this->router->aliasMiddleware($key, $middleware);
        }
    }

    /**
     * 收到 Http 处理请求时调用
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function onRequest($server, $serverSocket, $request, $response)
    {
        $httpRequest = $this->createHttpRequest($request);

        $respondent = $this->createRespondent($response);

        try {
            $this->app->instance('respondent', $respondent);
            $this->app->instance('request', $httpRequest);

            $httpRequest->attributes->add([
                'request'    => $request,
                'response'   => $response,
                'respondent' => $respondent
            ]);

            $httpRequest->enableHttpMethodParameterOverride();

            $httpResponse = $this->sendRequestThroughRouter($httpRequest);
        } catch (Exception $e) {
            $httpResponse = $this->handleException($httpRequest, $e);
        } catch (Throwable $e) {
            $httpResponse = $this->handleException($httpRequest, new FatalThrowableError($e));
        }

        $this->app->forgetInstance('respondent');

        $this->sendResponse($respondent, $httpResponse);

        $this->app->instance('request', $this->consoleRequest);
    }

    /**
     * 创建 Http 请求
     *
     * @param \Swoole\Http\Request $request
     *
     * @return \Illuminate\Http\Request
     */
    protected function createHttpRequest($request)
    {
        return new Request(
            $request->get ?? [], $request->post ?? [], [], $request->cookie ?? [], $request->files ?? [],
            $this->getRequestServer($request), $request->rawContent()
        );
    }

    /**
     * 获得请求 $_SERVER 形式参数
     *
     * @param \Swoole\Http\Request $request
     *
     * @return array
     */
    protected function getRequestServer($request)
    {
        $server = [];

        // 解析 $request->server
        foreach (($request->server ?? []) as $name => $value) {
            $server[strtoupper($name)] = $value;
        }

        // 解析 $request->header
        foreach (($request->header ?? []) as $name => $value) {
            $server['HTTP_'.strtoupper($name)] = $value;
        }

        return $server;
    }

    /**
     * 创建响应发送器
     *
     * @param \Swoole\Http\Response $response
     *
     * @return \Lawoole\Http\Respondent
     */
    protected function createRespondent($response)
    {
        return new Respondent($response);
    }

    /**
     * 发送响应体
     *
     * @param \Lawoole\Http\Respondent $respondent
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    protected function sendResponse($respondent, $response)
    {
        if ($response instanceof AsyncResponse) {
            return;
        }

        $respondent->sendHeader($response->getStatusCode(), $response->headers);

        $respondent->sendBody($response->getContent());
    }

    /**
     * 向路由器发送请求
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendRequestThroughRouter($request)
    {
        $this->setGlobalRequest($request);

        $handler = $this->dispatchToRouter();

        if (count($this->middleware) > 0) {
            $pipeline = new Pipeline($this->app);

            return $pipeline->send($request)->through($this->middleware)->then($handler);
        }

        return $handler($request);
    }

    /**
     * 获得路由调度调用
     *
     * @return \Closure
     */
    protected function dispatchToRouter()
    {
        return function ($request) {
            return $this->router->dispatch($request);
        };
    }

    /**
     * 设置全局请求对象
     *
     * @param \Illuminate\Http\Request $request
     */
    protected function setGlobalRequest($request)
    {
        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');
    }

    /**
     * 处理异常
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleException($request, Exception $e)
    {
        $handler = $this->app->make(ExceptionHandler::class);

        $handler->report($e);

        return $handler->render($request, $e);
    }
}
