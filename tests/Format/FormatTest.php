<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Format;

use Innmind\Rest\Server\{
    Format\Format,
    Format\MediaType,
    Exception\DomainException,
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
        $this->assertSame('json', $format->toString());
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

    public function testThrowWhenInvalidMediaType()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type Set<Innmind\Rest\Server\Format\MediaType>');

        new Format('foo', Set::of('string'), 42);
    }

    public function testThrowWhenNoMediaType()
    {
        $this->expectException(DomainException::class);

        new Format('foo', Set::of(MediaType::class), 42);
    }
}
