<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Format;

use Innmind\Rest\Server\Format\MediaType;

class MediaTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $m = new MediaType($s = 'application/vnd.media-type+suffix', 42);

        $this->assertSame($s, $m->mime());
        $this->assertSame($s, (string) $m);
        $this->assertSame('application', $m->topLevel());
        $this->assertSame('vnd.media-type', $m->subType());
        $this->assertSame('suffix', $m->suffix());
        $this->assertSame(42, $m->priority());
    }

    /**
     * @expectedException Innmind\Filesystem\Exception\InvalidMediaTypeStringException
     */
    public function testThrowWhenInvalidMediaTypeGiven()
    {
        new MediaType('foo', 42);
    }
}
