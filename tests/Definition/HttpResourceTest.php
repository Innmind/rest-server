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
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class HttpResourceTest extends TestCase
{
    public function testInterface()
    {
        $resource = HttpResource::rangeable(
            'foobar',
            $identity = new Identity('foo'),
            $properties = (new Map('string', Property::class)),
            $options = new Map('scalar', 'variable'),
            $metas = new Map('scalar', 'variable'),
            $gateway = new Gateway('bar'),
            $links = new Map('string', 'string')
        );

        $this->assertInstanceOf(Name::class, $resource->name());
        $this->assertSame('foobar', (string) $resource->name());
        $this->assertSame('foobar', (string) $resource);
        $this->assertSame($identity, $resource->identity());
        $this->assertSame($properties, $resource->properties());
        $this->assertSame($options, $resource->options());
        $this->assertSame($metas, $resource->metas());
        $this->assertSame($gateway, $resource->gateway());
        $this->assertTrue($resource->isRangeable());
        $this->assertSame($links, $resource->allowedLinks());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 3 must be of type MapInterface<string, Innmind\Rest\Server\Definition\Property>
     */
    public function testThrowForInvalidPropertyMap()
    {
        new HttpResource(
            'foobar',
            new Identity('foo'),
            new Map('string', 'string'),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Gateway('bar'),
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
            new Identity('foo'),
            new Map('string', Property::class),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Gateway('bar'),
            new Map('int', 'int')
        );
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 4 must be of type MapInterface<scalar, variable>
     */
    public function testThrowForInvalidOptionMap()
    {
        new HttpResource(
            'foobar',
            new Identity('foo'),
            new Map('string', Property::class),
            new Map('string', 'string'),
            new Map('scalar', 'variable'),
            new Gateway('bar'),
            new Map('string', 'string')
        );
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 5 must be of type MapInterface<scalar, variable>
     */
    public function testThrowForInvalidMetaMap()
    {
        new HttpResource(
            'foobar',
            new Identity('foo'),
            new Map('string', Property::class),
            new Map('scalar', 'variable'),
            new Map('string', 'string'),
            new Gateway('bar'),
            new Map('string', 'string')
        );
    }
}
