<?php
namespace Lawoole\Http;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Pipeline;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class HttpServerSocketHandler
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * The global HTTP middleware.
     *
     * @var array
     */
    protected $middleware;

    /**
     * The priority-sorted list of middleware.
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
     * Create a Http server event handler instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Illuminate\Routing\Router $router
     */
    public function __construct(Application $app, Router $router)
    {
        $this->app = $app;
        $this->router = $router;

        $this->loadMiddleware();
    }

    /**
     * Load all configured middleware.
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
     * Called when the server receive a Http request.
     *
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function onRequest($server, $serverSocket, $request, $response)
    {
        $swapHttpRequest = $this->app['request'];

        $httpRequest = $this->createHttpRequest($request);

        $respondent = $this->createRespondent($response);

        try {
            $this->app->instance('respondent', $respondent);

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

        $this->app->instance('request', $swapHttpRequest);
    }

    /**
     * Create a Http request from the Swoole request.
     *
     * @param \Swoole\Http\Request $request
     *
     * @return \Illuminate\Http\Request
     */
    protected function createHttpRequest($request)
    {
        return new Request(
            $request->get ?? [], $request->post ?? [], [], $request->cookie ?? [], $request->files ?? [],
            $this->parseRequestServer($request), $request->rawContent()
        );
    }

    /**
     * Get the parameters like $_SERVER in Swoole request.
     *
     * @param \Swoole\Http\Request $request
     *
     * @return array
     */
    protected function parseRequestServer($request)
    {
        $server = [];

        foreach (($request->server ?? []) as $name => $value) {
            $server[strtoupper($name)] = $value;
        }

        foreach (($request->header ?? []) as $name => $value) {
            $server['HTTP_'.strtoupper($name)] = $value;
        }

        return $server;
    }

    /**
     * Create a respondent instance.
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
     * Send response.
     *
     * @param \Lawoole\Http\Respondent $respondent
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    protected function sendResponse($respondent, $response)
    {
        $respondent->sendHeader($response->getStatusCode(), $response->headers);

        $respondent->sendBody($response->getContent());
    }

    /**
     * Send the given request through the middleware / router.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendRequestThroughRouter($request)
    {
        $this->app->instance('request', $request);

        Facade::clearResolvedInstance('request');

        $handler = $this->dispatchToRouter();

        if (count($this->middleware) > 0) {
            $pipeline = new Pipeline($this->app);

            return $pipeline->send($request)->through($this->middleware)->then($handler);
        }

        return $handler($request);
    }

    /**
     * Get the route dispatcher callback.
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
     * Report and render the exception to a response.
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
