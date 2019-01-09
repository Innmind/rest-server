<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Definition\{
    HttpResource,
    Identity,
    Gateway,
    Property,
    Name,
};
use Innmind\Immutable\{
    MapInterface,
    Map,
    Set,
};
use PHPUnit\Framework\TestCase;

class HttpResourceTest extends TestCase
{
    public function testInterface()
    {
        $resource = HttpResource::rangeable(
            'foobar',
            $gateway = new Gateway('bar'),
            $identity = new Identity('foo'),
            new Set(Property::class),
            $options = new Map('scalar', 'variable'),
            $metas = new Map('scalar', 'variable'),
            $links = new Map('string', 'string')
        );

        $this->assertInstanceOf(Name::class, $resource->name());
        $this->assertSame('foobar', (string) $resource->name());
        $this->assertSame('foobar', (string) $resource);
        $this->assertSame($identity, $resource->identity());
        $this->assertInstanceOf(MapInterface::class, $resource->properties());
        $this->assertSame('string', (string) $resource->properties()->keyType());
        $this->assertSame(Property::class, (string) $resource->properties()->valueType());
        $this->assertSame($options, $resource->options());
        $this->assertSame($metas, $resource->metas());
        $this->assertSame($gateway, $resource->gateway());
        $this->assertTrue($resource->isRangeable());
        $this->assertSame($links, $resource->allowedLinks());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 4 must be of type SetInterface<Innmind\Rest\Server\Definition\Property>
     */
    public function testThrowForInvalidPropertySet()
    {
        new HttpResource(
            'foobar',
            new Gateway('bar'),
            new Identity('foo'),
            new Set('string'),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Map('string', 'string')
        );
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 8 must be of type MapInterface<string, string>
     */
    public function testThrowForInvalidLinkMap()
    {
        new HttpResource(
            'foobar',
            new Gateway('bar'),
            new Identity('foo'),
            new Set(Property::class),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Map('int', 'int')
        );
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 5 must be of type MapInterface<scalar, variable>
     */
    public function testThrowForInvalidOptionMap()
    {
        new HttpResource(
            'foobar',
            new Gateway('bar'),
            new Identity('foo'),
            new Set(Property::class),
            new Map('string', 'string'),
            new Map('scalar', 'variable'),
            new Map('string', 'string')
        );
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 6 must be of type MapInterface<scalar, variable>
     */
    public function testThrowForInvalidMetaMap()
    {
        new HttpResource(
            'foobar',
            new Gateway('bar'),
            new Identity('foo'),
            new Set(Property::class),
            new Map('scalar', 'variable'),
            new Map('string', 'string'),
            new Map('string', 'string')
        );
    }
}
