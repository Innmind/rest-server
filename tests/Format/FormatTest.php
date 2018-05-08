<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Format;

use Innmind\Rest\Server\Format\{
    Format,
    MediaType,
};
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class FormatTest extends TestCase
{
    public function testInterface()
    {
        $format = new Format(
            'json',
            $types = Set::of(MediaType::class, new MediaType('application/json', 42)),
            24
        );

        $this->assertSame('json', $format->name());
        $this->assertSame('json', (string) $format);
        $this->assertSame($types, $format->mediaTypes());
        $this->assertSame(24, $format->priority());
    }

    public function testPreferredMediaType()
    {
        $format = new Format(
            'json',
            Set::of(
                MediaType::class,
                new MediaType('application/json', 42),
                new MediaType('text/json', 0)
            ),
            24
        );

        $media = $format->preferredMediaType();
        $this->assertInstanceOf(MediaType::class, $media);
        $this->assertSame('application/json', $media->mime());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 2 must be of type SetInterface<Innmind\Rest\Server\Format\MediaType>
     */
    public function testThrowWhenInvalidMediaType()
    {
        new Format('foo', new Set('string'), 42);
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\DomainException
     */
    public function testThrowWhenNoMediaType()
    {
        new Format('foo', new Set(MediaType::class), 42);
    }
}
