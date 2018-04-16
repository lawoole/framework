<?php
namespace Lawoole\Homer;

use Lawoole\Homer\Concerns\SerializeException;
use Throwable;

class Result
{
    use SerializeException;

    /**
     * 调用结果值
     *
     * @var mixed
     */
    protected $value;

    /**
     * 调用异常
     *
     * @var \Throwable
     */
    protected $exception;

    /**
     * 创建调用结果对象
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
     * 获得调用结果值
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * 设置调用结果值
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * 获得调用异常
     *
     * @return \Throwable
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * 设置调用异常
     *
     * @param \Throwable $exception
     */
    public function setException(Throwable $exception)
    {
        $this->exception = $exception;
    }

    /**
     * 判断否存在异常
     *
     * @return bool
     */
    public function hasException()
    {
        return $this->exception !== null;
    }

    /**
     * 恢复调用结果
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
