<?php
namespace Lawoole\Homer\Serialize\Serializations;

use Carbon\Carbon;
use DateTimeInterface;

class DateTimeSerialization extends Serialization
{
    /**
     * The timestamp of time.
     *
     * @var int
     */
    protected $timestamp;

    /**
     * The timezone string.
     *
     * @var string
     */
    protected $timezone;

    /**
     * DateTimeSerialization constructor.
     *
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(DateTimeInterface $dateTime)
    {
        $this->timestamp = $dateTime->getTimestamp();
        $this->timezone = $dateTime->getTimezone()->getName();
    }

    /**
     * Recover the DateTime instance from the serialization.
     *
     * @return mixed
     */
    public function recover()
    {
        return Carbon::createFromTimestamp($this->timestamp, $this->timezone);
    }
}