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
}