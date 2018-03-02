<?php
namespace Lawoole\Server\Concerns;

use Closure;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

trait HasEventHandler
{
    /**
     * 事件处理器
     *
     * @var \Closure
     */
    protected $eventHandler;

    /**
     * 设置事件处理器
     *
     * @param object|callable $handler
     */
    public function setEventHandler($handler)
    {
        if (!$handler instanceof Closure) {
            // 对非闭包的处理器进行包裹
            $handler = function ($event, $arguments) use ($handler) {
                if (is_object($handler) && method_exists($handler, $method = 'on'.$event)) {
                    return call_user_func_array([$handler, $method], $arguments);
                }

                if (is_callable($handler)) {
                    return call_user_func($handler, $event, ...$arguments);
                }

                return null;
            };
        }

        $this->eventHandler = $handler;
    }

    /**
     * 注册事件回调
     *
     * @param array $events
     */
    protected function registerEventCallbacks(array $events)
    {
        foreach ($events as $event) {
            if (method_exists($this, $method = 'register'.$event.'Callback')) {
                call_user_func([$this, $method]);
            }
        }
    }

    /**
     * 分发事件
     *
     * @param string $event
     * @param array $arguments
     */
    public function dispatchEvent($event, ...$arguments)
    {
        if ($this->eventHandler == null) {
            return;
        }

        try {
            call_user_func($this->eventHandler, $event, $arguments);
        } catch (Exception $e) {
            $this->handleDispatchingException($e);
        } catch (Throwable $e) {
            $this->handleDispatchingException(new FatalThrowableError($e));
        }
    }

    /**
     * 处理异常
     *
     * @param \Exception $e
     */
    protected function handleDispatchingException(Exception $e)
    {
        $handler = app(ExceptionHandler::class);

        $handler->report($e);

        $output = app(OutputInterface::class);

        $handler->renderForConsole($output, $e);
    }
}
