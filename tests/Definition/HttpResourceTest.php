<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\{
    Definition\HttpResource,
    Definition\Identity,
    Definition\Gateway,
    Definition\Property,
    Definition\Name,
    Definition\Locator,
    Action,
    Link,
    Reference,
    Identity as IdentityInterface,
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
            Set::of(Action::class, Action::get()),
            null,
            $metas = new Map('scalar', 'variable')
        );

        $this->assertInstanceOf(Name::class, $resource->name());
        $this->assertSame('foobar', (string) $resource->name());
        $this->assertSame('foobar', (string) $resource);
        $this->assertSame($identity, $resource->identity());
        $this->assertInstanceOf(MapInterface::class, $resource->properties());
        $this->assertSame('string', (string) $resource->properties()->keyType());
        $this->assertSame(Property::class, (string) $resource->properties()->valueType());
        $this->assertTrue($resource->allow(Action::options()));
        $this->assertTrue($resource->allow(Action::get()));
        $this->assertFalse($resource->allow(Action::create()));
        $this->assertSame($metas, $resource->metas());
        $this->assertSame($gateway, $resource->gateway());
        $this->assertTrue($resource->isRangeable());
    }

    public function testAccept()
    {
        $directory = require 'fixtures/mapping.php';
        $locator = new Locator($directory);

        $resource = $directory->definition('image');

        $this->assertTrue($resource->accept(
            $locator,
            new Link(
                new Reference(
                    $directory->definition('image'),
                    $this->createMock(IdentityInterface::class)
                ),
                'alternate'
            )
        ));
        $this->assertFalse($resource->accept(
            $locator,
            new Link(
                new Reference(
                    $directory->definition('image'),
                    $this->createMock(IdentityInterface::class)
                ),
                'alternate'
            ),
            new Link(
                new Reference(
                    $directory->definition('image'),
                    $this->createMock(IdentityInterface::class)
                ),
                'canonical'
            )
        ));
    }

    public function testThrowForInvalidPropertySet()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 4 must be of type SetInterface<Innmind\Rest\Server\Definition\Property>');

        new HttpResource(
            'foobar',
            new Gateway('bar'),
            new Identity('foo'),
            new Set('string')
        );
    }

    public function testThrowForInvalidLinkSet()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 6 must be of type SetInterface<Innmind\Rest\Server\Definition\AllowedLink>');

        new HttpResource(
            'foobar',
            new Gateway('bar'),
            new Identity('foo'),
            new Set(Property::class),
            new Set(Action::class),
            new Set('string')
        );
    }

    public function testThrowForInvalidOptionMap()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 5 must be of type SetInterface<Innmind\Rest\Server\Action>');

        new HttpResource(
            'foobar',
            new Gateway('bar'),
            new Identity('foo'),
            new Set(Property::class),
            new Set('string')
        );
    }

    public function testThrowForInvalidMetaMap()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 7 must be of type MapInterface<scalar, variable>');

        new HttpResource(
            'foobar',
            new Gateway('bar'),
            new Identity('foo'),
            new Set(Property::class),
            new Set(Action::class),
            null,
            new Map('string', 'string')
        );
    }
}
