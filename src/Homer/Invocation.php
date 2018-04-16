<?php
namespace Lawoole\Homer;

use Lawoole\Homer\Concerns\HasAttachments;

class Invocation
{
    use HasAttachments;

    /**
     * 调用接口名
     *
     * @var string
     */
    protected $interface;

    /**
     * 调用方法名
     *
     * @var string
     */
    protected $method;

    /**
     * 调用参数
     *
     * @var array
     */
    protected $arguments;

    /**
     * 新建调用对象实例
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
     * 获得调用接口类名
     *
     * @return string
     */
    public function getInterface()
    {
        return $this->interface;
    }

    /**
     * 设置调用的接口名
     *
     * @param string $interface
     */
    public function setInterface($interface)
    {
        $this->interface = $interface;
    }

    /**
     * 获得调用方法名
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * 设置调用方法名
     *
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * 获得调用参数
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * 设置调用参数
     *
     * @param array $arguments
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = array_values($arguments);
    }
}
