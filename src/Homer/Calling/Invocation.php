<?php
namespace Lawoole\Homer\Calling;

use Lawoole\Contracts\Support\Attachable;
use Lawoole\Homer\Serialize\Serializable;
use Lawoole\Support\HasAttachments;

class Invocation implements Attachable
{
    use HasAttachments, Serializable;

    /**
     * The interface name calling to.
     *
     * @var string
     */
    protected $interface;

    /**
     * The calling method name.
     *
     * @var string
     */
    protected $method;

    /**
     * The invocation arguments.
     *
     * @var array
     */
    protected $arguments;

    /**
     * Create a new invocation instance.
     *
     * @param string $interface
     * @param string $method
     * @param array $arguments
     * @param array $attachments
     */
    public function __construct($interface, $method, array $arguments = [], array $attachments = [])
    {
        $this->interface = $interface;
        $this->method = $method;
        $this->arguments = $arguments;

        $this->setAttachments($attachments);
    }

    /**
     * Get the calling interface name.
     *
     * @return string
     */
    public function getInterface()
    {
        return $this->interface;
    }

    /**
     * Set the calling interface name.
     *
     * @param string $interface
     */
    public function setInterface($interface)
    {
        $this->interface = $interface;
    }

    /**
     * Get the calling method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the calling method name.
     *
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Get the calling arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Set the calling arguments.
     *
     * @param array $arguments
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = array_values($arguments);
    }
}
