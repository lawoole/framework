<?php
namespace Lawoole\Support;

/**
 * 00000000000000000000000000000000 | 00000 | 00000 | 00000000000 | 00000000000
 * -------- Time in second -------- | - M - | - R - | - Process - | --- Inc ---
 */
class Snowflake
{
    /**
     * 最后生成时间
     *
     * @var int
     */
    protected $lastSecond = 0;

    /**
     * 常量掩码
     *
     * @var int
     */
    protected $mask;

    /**
     * 机器编码
     *
     * @var int
     */
    protected $machineId = 0;

    /**
     * 随机码
     *
     * @var int
     */
    protected $randomId = 0;

    /**
     * 进程编码
     *
     * @var int
     */
    protected $processId = 0;

    /**
     * 自增计数
     *
     * @var int
     */
    protected $sequence = 0;

    /**
     * 创建雪花生成器
     */
    public function __construct()
    {
        // 设置机器码
        if (function_exists('swoole_get_local_mac')) {
            $this->setMachineId(crc32(serialize(swoole_get_local_mac())));
        }

        // 设置进程码
        $this->setProcessId(getmypid());

        // 设置随机码
        $this->setRandomId(random_int(0, 0x1f));
    }

    /**
     * 更新常量掩码
     */
    protected function updateMask()
    {
        $this->mask = ($this->machineId << 27) | ($this->randomId << 22) | ($this->processId << 11);
    }

    /**
     * 设置机器编码
     *
     * @param int $machineId
     */
    public function setMachineId($machineId)
    {
        $this->machineId = $machineId & 0x1f;
    }

    /**
     * 设置随机码
     *
     * @param int $randomId
     */
    public function setRandomId($randomId)
    {
        $this->randomId = $randomId & 0x1f;
    }

    /**
     * 设置进程编码
     *
     * @param int $processId
     */
    public function setProcessId($processId)
    {
        $this->processId = $processId & 0x7ff;
    }

    /**
     * 生成唯一编码
     *
     * @return string
     */
    public function generate()
    {
        // 时间戳
        $timestamp = time();

        // 自增数
        if ($this->lastSecond == $timestamp) {
            $sequence = ++ $this->sequence;

            if ($sequence > 0x7ff) {
                $this->lastSecond = ++ $timestamp;

                // 重置自增
                $sequence = $this->sequence = 0;
            }
        } else {
            $this->lastSecond = $timestamp;
            $sequence = $this->sequence = 0;
        }

        return ($timestamp << 32) | $this->mask | ($sequence & 0x7ff);
    }
}
