<?php
namespace Lawoole\Foundation\Http;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Lawoole\Contracts\Foundation\ApplicationInterface;
use Lawoole\Routing\RequestManager;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class Kernel implements KernelContract
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\ApplicationInterface
     */
    protected $app;

    /**
     * 初始化过程集合
     *
     * @var array
     */
    protected $bootstrappers = [
        \Lawoole\Foundation\Bootstrap\LoadConfigurations::class,
        \Lawoole\Foundation\Bootstrap\RegisterExceptionHandlers::class,
        \Lawoole\Foundation\Bootstrap\RegisterFacades::class,
        \Lawoole\Foundation\Bootstrap\RegisterServiceProviders::class,
        \Lawoole\Foundation\Bootstrap\BootProviders::class
    ];

    /**
     * 创建 Http 处理核心
     *
     * @param \Lawoole\Contracts\Foundation\ApplicationInterface $app
     */
    public function __construct(ApplicationInterface $app)
    {
        $this->app = $app;
    }

    /**
     * 初始化处理核心
     */
    public function bootstrap()
    {
        $this->app->bootstrapWith($this->bootstrappers);
    }

    /**
     * 处理 Http 请求
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function handle($request)
    {
        try {
            // 初始化
            $this->bootstrap();

            $requestManager = $this->createRequestManager($request);

            // 处理请求
            $response = $requestManager->handle();
        } catch (Exception $e) {
            // 处理异常
            $response = $this->handleException($request, $e);
        } catch (Throwable $e) {
            // 转换为异常
            $e = new FatalThrowableError($e);

            // 处理异常
            $response = $this->handleException($request, $e);
        }

        return $response;
    }

    /**
     * 创建请求处理器
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Lawoole\Routing\RequestManager
     */
    protected function createRequestManager($request)
    {
        return new RequestManager($this->app, $request, function ($response) {
            // 发送响应
            $response->send();

            // 如果存在 fastcgi_finish_request 函数，调用它来完成响应发送，提高响应速度
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
        });
    }

    /**
     * 终止请求处理
     *
     * @param \Illuminate\Http\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function terminate($request, $response)
    {
        $this->app->terminate();
    }

    /**
     * 获得服务容器实例
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    public function getApplication()
    {
        return $this->app;
    }

    /**
     * 处理异常
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    protected function handleException($request, Exception $e)
    {
        try {
            $handler = $this->app->make(ExceptionHandler::class);
        } catch (Exception $ex) {
            throw $e;
        }

        $handler->report($e);

        $response = $handler->render($request, $e);

        // 发送异常响应
        $response->send();

        return $response;
    }
}
