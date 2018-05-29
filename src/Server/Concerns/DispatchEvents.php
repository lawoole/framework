<?php
namespace Lawoole\Server\Concerns;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

trait DispatchEvents
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * 事件处理器
     *
     * @var mixed
     */
    protected $handler;

    /**
     * 分发事件
     *
     * @param string $event
     * @param array $arguments
     */
    public function dispatchEvent($event, ...$arguments)
    {
        if ($this->handler == null) {
            return;
        }

        try {
            if (method_exists($this->handler, $method = "on{$event}")) {
                call_user_func_array([$this->handler, $method], $arguments);
            }
        } catch (Exception $e) {
            $this->handleException($e);
        } catch (Throwable $e) {
            $this->handleException(new FatalThrowableError($e));
        }
    }

    /**
     * 处理异常
     *
     * @param \Exception $e
     */
    protected function handleException(Exception $e)
    {
        $handler = $this->app->make(ExceptionHandler::class);

        $handler->report($e);

        $handler->renderForConsole($this->output, $e);
    }
}
