<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Formats,
    Format\Format,
    Format\MediaType
};
use Innmind\Immutable\{
    Map,
    Set,
    SetInterface
};
use PHPUnit\Framework\TestCase;

class FormatsTest extends TestCase
{
    public function testInterface()
    {
        $fs = new Formats(
            $m = (new Map('string', Format::class))
                ->put(
                    'json',
                    $f = new Format(
                        'json',
                        (new Set(MediaType::class))
                            ->add(new MediaType('application/json', 42)),
                        42
                    )
                )
        );

        $this->assertSame($m, $fs->all());
        $this->assertSame($f, $fs->get('json'));
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidMapKey()
    {
        new Formats(new Map('int', Format::class));
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidMapValue()
    {
        new Formats(new Map('string', 'string'));
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenEmptyMap()
    {
        new Formats(new Map('string', Format::class));
    }

    public function testMediaTypes()
    {
        $fs = new Formats(
            (new Map('string', Format::class))
                ->put(
                    'json',
                    new Format(
                        'json',
                        (new Set(MediaType::class))
                            ->add($json = new MediaType('application/json', 42)),
                        42
                    )
                )
                ->put(
                    'html',
                    new Format(
                        'html',
                        (new Set(MediaType::class))
                            ->add($html = new MediaType('text/html', 40))
                            ->add($xhtml = new MediaType('text/xhtml', 0)),
                        0
                    )
                )
        );

        $types = $fs->mediaTypes();
        $this->assertInstanceOf(SetInterface::class, $types);
        $this->assertSame(MediaType::class, (string) $types->type());
        $this->assertSame(3, $types->size());
        $this->assertTrue($types->contains($json));
        $this->assertTrue($types->contains($html));
        $this->assertTrue($types->contains($xhtml));
    }

    public function testFromMediaType()
    {
        $fs = new Formats(
            (new Map('string', Format::class))
                ->put(
                    'json',
                    $j = new Format(
                        'json',
                        (new Set(MediaType::class))
                            ->add(new MediaType('application/json', 42)),
                        42
                    )
                )
                ->put(
                    'html',
                    $h = new Format(
                        'html',
                        (new Set(MediaType::class))
                            ->add(new MediaType('text/html', 40))
                            ->add(new MediaType('text/xhtml', 0)),
                        0
                    )
                )
        );

        $this->assertSame($j, $fs->fromMediaType('application/json'));
        $this->assertSame($h, $fs->fromMediaType('text/html'));
        $this->assertSame($h, $fs->fromMediaType('text/xhtml'));
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenNoFormatForWishedMediaType()
    {
        $fs = new Formats(
            (new Map('string', Format::class))
                ->put(
                    'html',
                    new Format(
                        'html',
                        (new Set(MediaType::class))
                            ->add(new MediaType('text/html', 40))
                            ->add(new MediaType('text/xhtml', 0)),
                        0
                    )
                )
        );

        $fs->fromMediaType('application/json');
    }

    public function testMatching()
    {
        $fs = new Formats(
            (new Map('string', Format::class))
                ->put(
                    'json',
                    $j = new Format(
                        'json',
                        (new Set(MediaType::class))
                            ->add(new MediaType('application/json', 42)),
                        42
                    )
                )
                ->put(
                    'html',
                    $h = new Format(
                        'html',
                        (new Set(MediaType::class))
                            ->add(new MediaType('text/html', 40))
                            ->add(new MediaType('text/xhtml', 0)),
                        0
                    )
                )
        );

        $format = $fs->matching('text/html, application/json;q=0.5, *;q=0.1');

        $this->assertSame($h, $format);
    }

    public function testMatchingWhenAcceptingEverything()
    {
        $fs = new Formats(
            (new Map('string', Format::class))
                ->put(
                    'json',
                    $j = new Format(
                        'json',
                        (new Set(MediaType::class))
                            ->add(new MediaType('application/json', 42)),
                        42
                    )
                )
                ->put(
                    'html',
                    $h = new Format(
                        'html',
                        (new Set(MediaType::class))
                            ->add(new MediaType('text/html', 40))
                            ->add(new MediaType('text/xhtml', 0)),
                        0
                    )
                )
        );

        $format = $fs->matching('*');

        $this->assertSame($j, $format);
    }
}
