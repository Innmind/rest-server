<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Definition\{
    Directory,
    HttpResource,
    Identity,
    Property,
    Gateway,
    Name,
};
use Innmind\Immutable\{
    Map,
    MapInterface,
};
use PHPUnit\Framework\TestCase;

class DirectoryTest extends TestCase
{
    public function testInterface()
    {
        $directory = new Directory(
            'foo',
            $children = (new Map('string', Directory::class))
                ->put(
                    'bar',
                    $directory2 = new Directory(
                        'bar',
                        new Map('string', Directory::class),
                        new Map('string', HttpResource::class)
                    )
                ),
            $definitions = (new Map('string', HttpResource::class))
                ->put(
                    'res',
                    $resource = new HttpResource(
                        'res',
                        new Identity('uuid'),
                        new Map('string', Property::class),
                        new Map('scalar', 'variable'),
                        new Map('scalar', 'variable'),
                        new Gateway('foo'),
                        true,
                        new Map('string', 'string')
                    )
                )
        );

        $this->assertInstanceOf(Name::class, $directory->name());
        $this->assertSame('foo', (string) $directory->name());
        $this->assertSame('foo', (string) $directory);
        $this->assertSame($directory2, $directory->child('bar'));
        $this->assertSame($children, $directory->children());
        $this->assertSame($resource, $directory->definition('res'));
        $this->assertSame($definitions, $directory->definitions());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 2 must be of type MapInterface<string, Innmind\Rest\Server\Definition\Directory>
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
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 3 must be of type MapInterface<string, Innmind\Rest\Server\Definition\HttpResource>
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
        $directory = new Directory(
            'foo',
            (new Map('string', Directory::class))
                ->put(
                    'bar',
                    new Directory(
                        'bar',
                        new Map('string', Directory::class),
                        (new Map('string', HttpResource::class))
                            ->put(
                                'res',
                                $child = new HttpResource(
                                    'res',
                                    new Identity('uuid'),
                                    new Map('string', Property::class),
                                    new Map('scalar', 'variable'),
                                    new Map('scalar', 'variable'),
                                    new Gateway('foo'),
                                    true,
                                    new Map('string', 'string')
                                )
                            )
                    )
                ),
            (new Map('string', HttpResource::class))
                ->put(
                    'res',
                    $resource = new HttpResource(
                        'res',
                        new Identity('uuid'),
                        new Map('string', Property::class),
                        new Map('scalar', 'variable'),
                        new Map('scalar', 'variable'),
                        new Gateway('foo'),
                        true,
                        new Map('string', 'string')
                    )
                )
        );

        $defs = $directory->flatten();

        $this->assertInstanceOf(MapInterface::class, $defs);
        $this->assertSame('string', (string) $defs->keyType());
        $this->assertSame(HttpResource::class, (string) $defs->valueType());
        $this->assertTrue(
            $defs->equals(
                (new Map('string', HttpResource::class))
                    ->put('foo.res', $resource)
                    ->put('foo.bar.res', $child)
            )
        );
        $this->assertSame($defs, $directory->flatten());
    }
}
