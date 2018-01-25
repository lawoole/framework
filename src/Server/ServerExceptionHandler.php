<?php
namespace Lawoole\Server;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Lawoole\Console\OutputStyle;
use Lawoole\Swoole\Exception\ExceptionHandlerInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class ServerExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\ApplicationInterface
     */
    protected $app;

    /**
     * 控制台格式输出
     *
     * @var \Lawoole\Console\OutputStyle
     */
    protected $outputStyle;

    /**
     * 创建异常处理器
     *
     * @param \Lawoole\Contracts\Foundation\ApplicationInterface $app
     * @param \Lawoole\Console\OutputStyle $outputStyle
     */
    public function __construct($app, OutputStyle $outputStyle)
    {
        $this->app = $app;
        $this->outputStyle = $outputStyle;
    }

    /**
     * 处理异常
     *
     * @param \Throwable $throwable
     *
     * @throws \Throwable
     */
    public function handleException(Throwable $throwable)
    {
        if (!$throwable instanceof Exception) {
            $exception = new FatalThrowableError($throwable);
        } else {
            $exception = $throwable;
        }

        try {
            $handler = $this->app->make(ExceptionHandler::class);
        } catch (Exception $e) {
            // 抛出原始错误
            throw $exception;
        }

        $handler->report($exception);

        $handler->renderForConsole($this->outputStyle->getOutput(), $exception);
    }
}
