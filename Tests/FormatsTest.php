<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests;

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

class FormatsTest extends \PHPUnit_Framework_TestCase
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
}
