<?php
namespace Lawoole\Homer\Calling;

use Lawoole\Contracts\Support\Attachable;
use Lawoole\Homer\Serialize\Serializable;
use Lawoole\Support\HasAttachments;
use Throwable;

class Result implements Attachable
{
    use HasAttachments, Serializable;

    /**
     * The result data.
     *
     * @var mixed
     */
    protected $value;

    /**
     * The exception thrown while calling.
     *
     * @var \Throwable
     */
    protected $exception;

    /**
     * Create a calling result instance.
     *
     * @param mixed $value
     * @param \Throwable $exception
     */
    public function __construct($value = null, Throwable $exception = null)
    {
        $this->setValue($value);

        $this->setException($exception);
    }

    /**
     * Get the result data.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the result data.
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get the calling exception.
     *
     * @return \Throwable
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Set the calling exception.
     *
     * @param \Throwable $exception
     */
    public function setException(Throwable $exception = null)
    {
        $this->exception = $exception;
    }

    /**
     * Return whether result with a exception.
     *
     * @return bool
     */
    public function hasException()
    {
        return $this->exception !== null;
    }

    /**
     * Recover the result, a value will be return or a exception will be thrown.
     *
     * @return mixed
     *
     * @throws \Throwable
     */
    public function recover()
    {
        if ($this->hasException()) {
            throw $this->getException();
        }

        return $this->getValue();
    }
}
