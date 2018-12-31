<?php
namespace Lawoole\Contracts\Support;

interface EventDispatcher
{
    /**
     * Set the event handler.
     *
     * @param object|callable $handler
     */
    public function setEventHandler($handler);

    /**
     * Register an event callback.
     *
     * @param string $event
     * @param callable $callback
     */
    public function registerEventCallback($event, $callback);
}
