<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests\Format;

use Innmind\Rest\Server\Format\MediaType;

class MediaTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $m = new MediaType($s = 'type/vnd.media-type+suffix', 42);

        $this->assertSame($s, $m->mime());
        $this->assertSame($s, (string) $m);
        $this->assertSame(42, $m->priority());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidMediaTypeGiven()
    {
        new MediaType('foo', 42);
    }
}
