<?php
namespace Lawoole\Homer\Invokers;

use Lawoole\Homer\Invocation;
use Lawoole\Homer\Result;
use Throwable;

abstract class Invoker
{
    /**
     * 调用接口名
     *
     * @var string
     */
    protected $interface;

    /**
     * 选项
     *
     * @var array
     */
    protected $options;

    /**
     * 创建调用器
     *
     * @param string $interface
     * @param array $options
     */
    public function __construct($interface, array $options = [])
    {
        $this->interface = $interface;
        $this->options = $options;
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
     * 执行调用
     *
     * @param \Lawoole\Homer\Invocation $invocation
     *
     * @return \Lawoole\Homer\Result
     */
    public function invoke(Invocation $invocation)
    {
        try {
            return $this->doInvoke($invocation);
        } catch (Throwable $e) {
            return new Result(null, $e);
        }
    }

    /**
     * 执行调用并得到调用结果
     *
     * @param \Lawoole\Homer\Invocation $invocation
     *
     * @return \Lawoole\Homer\Result
     */
    abstract protected function doInvoke(Invocation $invocation);
}
