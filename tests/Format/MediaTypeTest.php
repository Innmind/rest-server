<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Format;

use Innmind\Rest\Server\Format\MediaType;
use Innmind\MediaType\Exception\Exception;
use PHPUnit\Framework\TestCase;

class MediaTypeTest extends TestCase
{
    public function testInterface()
    {
        $media = new MediaType($mime = 'application/vnd.media-type+suffix', 42);

        $this->assertSame($mime, $media->mime());
        $this->assertSame($mime, (string) $media);
        $this->assertSame('application', $media->topLevel());
        $this->assertSame('vnd.media-type', $media->subType());
        $this->assertSame('suffix', $media->suffix());
        $this->assertSame(42, $media->priority());
    }

    public function testThrowWhenInvalidMediaTypeGiven()
    {
        $this->expectException(Exception::class);

        new MediaType('foo', 42);
    }
}
