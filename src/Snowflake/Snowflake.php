<?php
namespace Lawoole\Snowflake;

use Lawoole\Contracts\Snowflake\Snowflake as SnowflakeContract;

/**
 * 00000000000000000000000000000000 | 00000 | 00000 | 00000000000 | 00000000000
 * -------- Time in second -------- | - M - | - R - | - Process - | --- Inc ---
 */
class Snowflake implements SnowflakeContract
{
    /**
     * The timestamp base number.
     *
     * @var int
     */
    const BASE_TIME = 1262275200;

    /**
     * Last generate time.
     *
     * @var int
     */
    protected $lastTimestamp = 0;

    /**
     * Constants mask.
     *
     * @var int
     */
    protected $mask;

    /**
     * The machine id.
     *
     * @var int
     */
    protected $machineId = 0;

    /**
     * The random id.
     *
     * @var int
     */
    protected $randomId = 0;

    /**
     * The process id.
     *
     * @var int
     */
    protected $processId = 0;

    /**
     * The sequence.
     *
     * @var int
     */
    protected $sequence = 0;

    /**
     * Create a snowflake id generator.
     */
    public function __construct()
    {
        if (function_exists('swoole_get_local_mac')) {
            // We set the machine id by local machine mac address if possible.
            $this->setMachineId(crc32(serialize(swoole_get_local_mac())));
        }

        $this->setProcessId(getmypid());

        $this->setRandomId(random_int(0, 0x1f));
    }

    /**
     * Update the constants mask.
     */
    protected function updateMask()
    {
        $this->mask = ($this->machineId << 27) | ($this->randomId << 22) | ($this->processId << 11);
    }

    /**
     * Set the machine id.
     *
     * @param int $machineId
     */
    public function setMachineId($machineId)
    {
        $this->machineId = $machineId & 0x1f;
    }

    /**
     * Set the random id.
     *
     * @param int $randomId
     */
    public function setRandomId($randomId)
    {
        $this->randomId = $randomId & 0x1f;
    }

    /**
     * Set the process id.
     *
     * @param int $processId
     */
    public function setProcessId($processId)
    {
        $this->processId = $processId & 0x7ff;
    }

    /**
     * Generate a new snowflake id.
     *
     * @return string
     */
    public function nextId()
    {
        $timestamp = time() - static::BASE_TIME;

        if ($this->lastTimestamp >= $timestamp) {
            $timestamp = $this->lastTimestamp;

            $sequence = ++ $this->sequence;

            if ($sequence > 0x7ff) {
                $this->lastTimestamp = ++ $timestamp;

                $sequence = $this->sequence = 0;
            }
        } else {
            $this->lastTimestamp = $timestamp;

            $sequence = $this->sequence = 0;
        }

        return ($timestamp << 32) | $this->mask | ($sequence & 0x7ff);
    }
}
