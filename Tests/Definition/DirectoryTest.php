<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\Definition\{
    Directory,
    HttpResource,
    Identity,
    Property,
    Gateway
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    Map,
    Collection,
    MapInterface
};

class DirectoryTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $d = new Directory(
            'foo',
            $ds = (new Map('string', Directory::class))
                ->put(
                    'bar',
                    $d2 = new Directory(
                        'bar',
                        new Map('string', Directory::class),
                        new Map('string', HttpResource::class)
                    )
                ),
            $hr = (new Map('string', HttpResource::class))
                ->put(
                    'res',
                    $r = new HttpResource(
                        'res',
                        new Identity('uuid'),
                        new Map('string', Property::class),
                        new Collection([]),
                        new Collection([]),
                        new Gateway('foo'),
                        Url::fromString('/')
                    )
                )
        );

        $this->assertSame('foo', $d->name());
        $this->assertSame('foo', (string) $d);
        $this->assertSame($d2, $d->child('bar'));
        $this->assertSame($ds, $d->children());
        $this->assertSame($r, $d->definition('res'));
        $this->assertSame($hr, $d->definitions());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenGivingInvalidChildren()
    {
        new Directory(
            '',
            new Map('string', 'string'),
            new Map('string', HttpResource::class)
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenGivingInvalidDefinitions()
    {
        new Directory(
            '',
            new Map('string', Directory::class),
            new Map('string', 'string')
        );
    }

    public function testFlatten()
    {
        $d = new Directory(
            'foo',
            (new Map('string', Directory::class))
                ->put(
                    'bar',
                    $d2 = new Directory(
                        'bar',
                        new Map('string', Directory::class),
                        (new Map('string', HttpResource::class))
                            ->put(
                                'res',
                                $rs = new HttpResource(
                                    'res',
                                    new Identity('uuid'),
                                    new Map('string', Property::class),
                                    new Collection([]),
                                    new Collection([]),
                                    new Gateway('foo'),
                                    Url::fromString('/')
                                )
                            )
                    )
                ),
            (new Map('string', HttpResource::class))
                ->put(
                    'res',
                    $r = new HttpResource(
                        'res',
                        new Identity('uuid'),
                        new Map('string', Property::class),
                        new Collection([]),
                        new Collection([]),
                        new Gateway('foo'),
                        Url::fromString('/')
                    )
                )
        );

        $defs = $d->flatten();

        $this->assertInstanceOf(MapInterface::class, $defs);
        $this->assertSame('string', (string) $defs->keyType());
        $this->assertSame(HttpResource::class, (string) $defs->valueType());
        $this->assertTrue(
            $defs->equals(
                (new Map('string', HttpResource::class))
                    ->put('foo_res', $r)
                    ->put('foo_bar_res', $rs)
            )
        );
    }
}
