<?php
namespace Tests\Homer\Serialize\Serializations;

use Carbon\Carbon;
use Lawoole\Homer\Serialize\Serializations\DateTimeSerialization;
use PHPUnit\Framework\TestCase;

class DateTimeSerializationTest extends TestCase
{
    public function dateTimeProvider()
    {
        return [
            [Carbon::create(1949, 10, 1, 14, 0, 0, 0)],
            [Carbon::today('Asia/Shanghai')],
            [Carbon::now('UTC')],
            [Carbon::now()],
        ];
    }

    /**
     * @dataProvider dateTimeProvider
     *
     * @param \DateTime $dateTime
     */
    public function testSerializing($dateTime)
    {
        $serialization = new DateTimeSerialization($dateTime);

        $recovered = $serialization->recover();

        $this->assertNotSame($dateTime, $recovered);

        $this->assertEquals($dateTime->getTimestamp(), $recovered->getTimestamp());
    }

    /**
     * @dataProvider dateTimeProvider
     *
     * @param \DateTime $dateTime
     */
    public function testOrdinarySerializing($dateTime)
    {
        $serialization = new DateTimeSerialization($dateTime);

        $ordinary = $serialization->getOrdinaryValue();

        $this->assertEquals($dateTime->getTimestamp(), Carbon::parse($ordinary)->getTimestamp());
    }
}