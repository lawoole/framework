<?php
namespace Tests\Stream;

use Lawoole\Stream\MemoryStream;
use PHPUnit\Framework\TestCase;

class MemoryStreamTest extends TestCase
{
    public function testReadAndWrite()
    {
        $stream = new MemoryStream;

        $this->assertEquals(3, $stream->write('Dog'));

        $stream->rewind();

        $this->assertEquals('Do', $stream->read(2));

        $stream->write('Cat');

        $stream->rewind();

        $this->assertEquals('DoC', $stream->read(3));

        $this->assertEquals('at', $stream->read(2));
    }

    public function testEof()
    {
        $stream = new MemoryStream;

        $this->assertTrue($stream->eof());

        $stream->write('Dog');

        $this->assertTrue($stream->eof());

        $stream->rewind();

        $this->assertFalse($stream->eof());
    }

    public function testCrosstalk()
    {
        $left = new MemoryStream;
        $right = new MemoryStream;

        $left->write('Dog');
        $left->rewind();

        $right->write('Cat');
        $right->rewind();

        $this->assertEquals('Dog', $left->read(3));
        $this->assertEquals('Cat', $right->read(3));
    }
}
