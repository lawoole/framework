<?php
namespace Lawoole\Foundation\Bootstrap;

use ErrorException;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Lawoole\Contracts\Foundation\Application;
use Lawoole\Routing\RequestManager;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Exception\FatalErrorException;

class RegisterExceptionHandlers
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * 注册异常处理器
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     */
    public function bootstrap(Application $app)
    {
        $this->app = $app;

        error_reporting(-1);

        register_shutdown_function([$this, 'handleShutdown']);

        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);

        if (!$app->runningInTesting()) {
            ini_set('display_errors', 'Off');
        }
    }

    /**
     * 转换错误为异常
     *
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     *
     * @throws \ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0)
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

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
     * 处理程序结束
     *
     * @throws \Exception
     */
    public function handleShutdown()
    {
        if (($error = error_get_last()) !== null && $this->isFatalError($error['type'])) {
            $this->handleException($this->createFatalErrorException($error, 0));
        }
    }

    /**
     * 创建致命错误异常
     *
     * @param array $error
     * @param int|null $traceOffset
     *
     * @return \Symfony\Component\Debug\Exception\FatalErrorException
     */
    protected function createFatalErrorException(array $error, $traceOffset = null)
    {
        return new FatalErrorException(
            $error['message'], $error['type'], 0, $error['file'], $error['line'], $traceOffset
        );
    }

    /**
     * 判断错误是否为致命错误
     *
     * @param int $type
     *
     * @return bool
     */
    protected function isFatalError($type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }
}
