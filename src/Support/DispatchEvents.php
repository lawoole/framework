<?php
namespace Lawoole\Support;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

trait DispatchEvents
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The event handler.
     *
     * @var mixed
     */
    protected $eventHandler;

    /**
     * The event callbacks.
     *
     * @var array
     */
    protected $eventCallbacks = [];

    /**
     * Get the event handler.
     *
     * @return mixed
     */
    public function getEventHandler()
    {
        return $this->eventHandler;
    }

    /**
     * Set the event handler.
     *
     * @param object|callable $handler
     */
    public function setEventHandler($handler)
    {
        $this->eventHandler = $handler;
    }

    /**
     * Get all callbacks for the given event.
     *
     * @param string $event
     *
     * @return array
     */
    public function getEventCallbacks($event = null)
    {
        if ($event === null) {
            return $this->eventCallbacks;
        }

        return $this->eventCallbacks[strtolower($event)] ?? [];
    }

    /**
     * Register an event callback.
     *
     * @param string $event
     * @param callable $callback
     */
    public function registerEventCallback($event, $callback)
    {
        $this->eventCallbacks[strtolower($event)][] = $callback;
    }

    /**
     * Dispatch the event.
     *
     * @param string $event
     * @param array $arguments
     */
    public function dispatchEvent($event, ...$arguments)
    {
        $this->dispatchEventToEventCallbacks($event, $arguments);

        $this->dispatchEventToEventHandler($event, $arguments);
    }

    /**
     * Dispatch the event to the event callbacks.
     *
     * @param string $event
     * @param array $arguments
     */
    protected function dispatchEventToEventCallbacks($event, $arguments)
    {
        foreach ($this->getEventCallbacks($event) as $callback) {
            try {
                call_user_func_array($callback, $arguments);
            } catch (Exception $e) {
                $this->handleException($e);
            } catch (Throwable $e) {
                $this->handleException(new FatalThrowableError($e));
            }
        }
    }

    /**
     * Dispatch the event to the event handler.
     *
     * @param string $event
     * @param array $arguments
     */
    protected function dispatchEventToEventHandler($event, $arguments)
    {
        if ($this->eventHandler == null || ! method_exists($this->eventHandler, $method = "on{$event}")) {
            return;
        }

        try {
            $this->eventHandler->$method(...$arguments);
        } catch (Exception $e) {
            $this->handleException($e);
        } catch (Throwable $e) {
            $this->handleException(new FatalThrowableError($e));
        }
    }

    /**
     * Handle the exception with the exception handler.
     *
     * @param \Exception $e
     */
    protected function handleException(Exception $e)
    {
        $handler = $this->app->make(ExceptionHandler::class);

        $handler->report($e);

        try {
            $handler->renderForConsole(
                $this->app->make(OutputInterface::class), $e
            );
        } catch (Throwable $e) {
            //
        }
    }
}
