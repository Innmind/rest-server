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
    Set,
};
use PHPUnit\Framework\TestCase;

class DirectoryTest extends TestCase
{
    public function testInterface()
    {
        $directory = Directory::of(
            'foo',
            Set::of(
                Directory::class,
                $directory2 = new Directory(
                    'bar',
                    Map::of('string', Directory::class),
                    Map::of('string', HttpResource::class)
                )
            ),
            $resource = HttpResource::rangeable(
                'res',
                new Gateway('foo'),
                new Identity('uuid'),
                Set::of(Property::class)
            )
        );

        $this->assertInstanceOf(Name::class, $directory->name());
        $this->assertSame('foo', (string) $directory->name());
        $this->assertSame('foo', (string) $directory);
        $this->assertSame($directory2, $directory->child('bar'));
        $this->assertCount(1, $directory->children());
        $this->assertSame($resource, $directory->definition('res'));
        $this->assertCount(1, $directory->definitions());
    }

    public function testThrowWhenGivingInvalidChildren()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type Map<string, Innmind\Rest\Server\Definition\Directory>');

        new Directory(
            '',
            Map::of('string', 'string'),
            Map::of('string', HttpResource::class)
        );
    }

    public function testThrowWhenGivingInvalidDefinitions()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 3 must be of type Map<string, Innmind\Rest\Server\Definition\HttpResource>');

        new Directory(
            '',
            Map::of('string', Directory::class),
            Map::of('string', 'string')
        );
    }

    public function testFlatten()
    {
        $directory = Directory::of(
            'foo',
            Set::of(
                Directory::class,
                new Directory(
                    'bar',
                    Map::of('string', Directory::class),
                    Map::of('string', HttpResource::class)
                        (
                            'res',
                            $child = HttpResource::rangeable(
                                'res',
                                new Gateway('foo'),
                                new Identity('uuid'),
                                Set::of(Property::class)
                            )
                        )
                )
            ),
            $resource = HttpResource::rangeable(
                'res',
                new Gateway('foo'),
                new Identity('uuid'),
                Set::of(Property::class)
            )
        );

        $defs = $directory->flatten();

        $this->assertInstanceOf(Map::class, $defs);
        $this->assertSame('string', (string) $defs->keyType());
        $this->assertSame(HttpResource::class, (string) $defs->valueType());
        $this->assertTrue(
            $defs->equals(
                Map::of('string', HttpResource::class)
                    ('foo.res', $resource)
                    ('foo.bar.res', $child)
            )
        );
        $this->assertSame($defs, $directory->flatten());
    }
}
