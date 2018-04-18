<?php
namespace Lawoole\Homer\Serialization;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Validator;
use ReflectionClass;
use RuntimeException;
use Throwable;

class ExceptionSerialization
{
    /**
     * 异常类名
     *
     * @var string
     */
    protected $class;

    /**
     * 异常信息
     *
     * @var string
     */
    protected $message;

    /**
     * 异常属性
     *
     * @var array
     */
    protected $properties;

    /**
     * 异常序列化包
     *
     * @param \Throwable $e
     */
    public function __construct(Throwable $e)
    {
        $this->setException($e);
    }

    /**
     * 设置异常类名
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * 获得异常类名
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * 设置异常信息
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * 获得异常信息
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * 设置异常属性
     *
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * 获得异常属性
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * 设置异常
     *
     * @param \Throwable $e
     */
    public function setException(Throwable $e)
    {
        $this->class = get_class($e);
        $this->message = $e->getMessage();

        $this->properties = [];

        try {
            $reflection = new ReflectionClass($this->class);

            foreach ($reflection->getProperties() as $property) {
                if ($property->isStatic()) {
                    continue;
                }

                $property->setAccessible(true);

                $propertyName = $property->getName();
                $propertyValue = $property->getValue($e);

                if ($propertyName == 'trace') {
                    // 抛弃堆栈信息的序列化，节约信息量
                    continue;
                }

                $this->properties[$propertyName] = $this->packPropertyValue($propertyValue);
            }
        } catch (Throwable $e) {
            //
        }
    }

    /**
     * 获得异常
     *
     * @return \Throwable
     */
    public function getException()
    {
        if (!class_exists($this->class)) {
            return new RuntimeException($this->class);
        }

        try {
            $reflection = new ReflectionClass($this->class);

            $e = $reflection->newInstanceWithoutConstructor();

            if (!$e instanceof Throwable) {
                throw new RuntimeException("Class [{$this->class}] is not an exception.");
            }

            foreach ($reflection->getProperties() as $property) {
                $propertyName = $property->getName();

                if (isset($this->properties[$propertyName])) {
                    $property->setAccessible(true);
                    $property->setValue($e, $this->unpackPropertyValue($this->properties[$propertyName]));
                }
            }

            return $e;
        } catch (Throwable $ex) {
            return new RuntimeException($this->message);
        }
    }

    /**
     * 打包属性值
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function packPropertyValue($value)
    {
        if ($value instanceof Container) {
            $value = new ContainerSerialization($value);
        } elseif ($value instanceof Validator) {
            $value = new ValidatorSerialization($value);
        } elseif ($value instanceof Throwable) {
            // new self($value); 异常不做嵌套，减少数据体积
            $value = null;
        } elseif ($value instanceof Closure) {
            $value = null;
        }

        return $value;
    }

    /**
     * 拆包属性值
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function unpackPropertyValue($value)
    {
        if ($value instanceof ContainerSerialization) {
            $value = $value->getContainer();
        } elseif ($value instanceof ValidatorSerialization) {
            $value = $value->getValidator();
        } elseif ($value instanceof self) {
            $value = $value->getException();
        }

        return $value;
    }
}
