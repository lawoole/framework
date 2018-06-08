<?php
namespace Lawoole\Foundation\Bootstrap;

use Exception;
use Illuminate\Foundation\Bootstrap\HandleExceptions as BaseHandleExceptions;

class HandleExceptions extends BaseHandleExceptions
{
    /**
     * 处理未捕获的异常
     *
     * @param \Exception $e
     *
     * @throws \Exception
     */
    public function handleException($e)
    {
        try {
            $handler = $this->app->make(ExceptionHandler::class);
        } catch (Exception $ex) {
            // 抛出原始错误
            throw $e;
        }

        $handler->report($e);

        // 正在处理请求时，渲染到响应
        if ($this->app->bound(RequestManager::class)) {
            $requestManager = $this->app->make(RequestManager::class);

            $response = $handler->render($requestManager->getRequest(), $e);

            // 发送响应
            $requestManager->sendResponse($response);
        }
        // 运行在控制台时，渲染到控制台
        elseif ($this->app->runningInConsole()) {
            $handler->renderForConsole(new ConsoleOutput, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function renderForConsole(Exception $e)
    {
        $handler = $this->getExceptionHandler();

        // If a HTTP request is handling, we will render the exception to the response.
        if ($this->app->bound('request.manager')) {

            $response = $handler->render();


        }

        $handler->renderForConsole($this->app['console.output'], $e);
    }
}
