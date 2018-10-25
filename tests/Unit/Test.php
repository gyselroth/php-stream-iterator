<?php

declare(strict_types=1);

/**
 * Stream iterator
 *
 * @copyright   Copryright (c) 2018 gyselroth GmbH (https://gyselroth.com)
 * @license     MIT https://opensource.org/licenses/MIT
 */

namespace StreamIterator\Testsuite;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use StreamIterator\StreamIterator;

class Test extends TestCase
{
    public function testIsNotWritable()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        $this->assertFalse($stream->isWritable());
    }

    public function testWriteFalse()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        $this->assertFalse($stream->write('foo'));
    }

    public function testIsReadable()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        $this->assertTrue($stream->isReadable());
    }

    public function testClose()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        $this->assertNull($stream->close());
    }

    public function testIsSeekable()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        $this->assertTrue($stream->isSeekable());
    }

    public function testReadFirst()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        $this->assertSame('0', $stream->read(1));
    }

    public function testRewind()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        $this->assertSame('01', $stream->read(2));
        $stream->rewind();
        $this->assertSame('0', $stream->read(1));
    }

    public function testGetContents()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        $this->assertSame('012345', $stream->getContents());
    }

    public function testToString()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        $this->assertSame('012345', (string) $stream);
    }

    public function testGetContentsStringifyCallback()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator, function ($item) {
            return '-'.$item;
        });

        $this->assertSame('-0-1-2-3-4-5', $stream->getContents());
    }

    public function testEofFalse()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        $this->assertFalse($stream->eof());
    }

    public function testEofTrue()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        (string) $stream;
        $this->assertTrue($stream->eof());
    }

    public function testTell()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        $stream->read(1);
        $this->assertSame(1, $stream->tell());
    }

    public function testCount()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        $this->assertSame(6, $stream->getSize());
    }

    public function testGetMetadataNull()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        $this->assertSame([], $stream->getMetadata());
    }

    public function testGetMetadataEmpty()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        $this->assertNull($stream->getMetadata('foo'));
    }

    public function testSeekTwo()
    {
        $iterator = new ArrayIterator(range(0, 5));
        $stream = new StreamIterator($iterator);
        $stream->seek(2);
        $this->assertSame('2', $stream->read(1));
    }
}
