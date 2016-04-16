<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests\Format;

use Innmind\Rest\Server\Format\{
    Format,
    MediaType
};
use Innmind\Immutable\Set;

class FormatTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $f = new Format(
            'json',
            $t = (new Set(MediaType::class))->add(new MediaType('application/json', 42)),
            24
        );

        $this->assertSame('json', $f->name());
        $this->assertSame('json', (string) $f);
        $this->assertSame($t, $f->mediaTypes());
        $this->assertSame(24, $f->priority());
    }

    public function testPreferredMediaType()
    {
        $f = new Format(
            'json',
            (new Set(MediaType::class))
                ->add(new MediaType('application/json', 42))
                ->add(new MediaType('text/json', 0)),
            24
        );

        $m = $f->preferredMediaType();
        $this->assertInstanceOf(MediaType::class, $m);
        $this->assertSame('application/json', $m->mime());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidMediaType()
    {
        new Format('foo', new Set('string'), 42);
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenNoMediaType()
    {
        new Format('foo', new Set(MediaType::class), 42);
    }
}