<?php
namespace Lawoole\Swoole;

use Closure;
use Illuminate\Support\Arr;
use Lawoole\Swoole\Exception\ExceptionHandlerInterface;
use Throwable;

trait HasEventHandlers
{
    /**
     * 事件处理器
     *
     * @var array
     */
    protected $eventHandlers = [];

    /**
     * 移除处理器
     *
     * @var \Lawoole\Swoole\Exception\ExceptionHandlerInterface
     */
    protected $exceptionHandler;

    /**
     * 设置异常处理器
     *
     * @param object|\Closure $handler
     */
    public function setEventHandler($handler)
    {
        $this->eventHandlers = [$handler];
    }

    /**
     * 增加事件处理器
     *
     * @param object|\Closure $handler
     */
    public function putEventHandler($handler)
    {
        foreach ($this->eventHandlers as $eventHandler) {
            if ($eventHandler === $handler) {
                return;
            }
        }

        $this->eventHandlers[] = $handler;
    }

    /**
     * 获得事件处理器
     *
     * @return mixed
     */
    public function getEventHandler()
    {
        return Arr::first($this->eventHandlers);
    }

    /**
     * 获得所有的事件处理器
     *
     * @return array
     */
    public function getEventHandlers()
    {
        return $this->eventHandlers;
    }

    /**
     * 移除事件处理器
     *
     * @param object|\Closure $handler
     */
    public function removeEventHandler($handler)
    {
        $this->eventHandlers = array_filter($this->eventHandlers, function ($eventHandler) use ($handler) {
            return $eventHandler !== $handler;
        });
    }

    /**
     * 移除所有的事件处理器
     */
    public function removeEventHandlers()
    {
        $this->eventHandlers = [];
    }

    /**
     * 设置异常处理器
     *
     * @param \Lawoole\Swoole\Exception\ExceptionHandlerInterface $exceptionHandler
     */
    public function setExceptionHandler(ExceptionHandlerInterface $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * 获得异常处理器
     *
     * @return \Lawoole\Swoole\Exception\ExceptionHandlerInterface
     */
    public function getExceptionHandler()
    {
        return $this->exceptionHandler;
    }

    /**
     * 移除异常处理器
     */
    public function removeException()
    {
        $this->exceptionHandler = null;
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
                $this->$method();
            }
        }
    }

    /**
     * 分发事件
     *
     * @param string $event
     * @param array $args
     */
    public function dispatchEvent($event, ...$args)
    {
        foreach ($this->eventHandlers as $handler) {
            // 分析事件处理器类型
            if ($handler instanceof Closure) {
                $callable = $handler;
            } elseif (method_exists($handler, $method = 'on'.$event)) {
                $callable = [$handler, $method];
            } else {
                $callable = null;
            }

            if ($callable) {
                try {
                    call_user_func_array($callable, $args);
                } catch (Throwable $throwable) {
                    try {
                        $this->handleException($throwable);
                    } catch (Throwable $tx) {
                        // 忽略处理过程中出现的问题
                    }
                }
            }
        }
    }

    /**
     * 处理异常
     *
     * @param \Throwable $throwable
     *
     * @throws \Throwable
     */
    protected function handleException(Throwable $throwable)
    {
        try {
            if ($this->exceptionHandler) {
                $this->exceptionHandler->handleException($throwable);
            }
        } catch (Throwable $tx) {
            throw $throwable;
        }
    }
}
