<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\HttpResource;

use Innmind\Rest\Server\{
    HttpResource\HttpResource,
    HttpResource as HttpResourceInterface,
    HttpResource\Property,
    Definition\HttpResource as Definition,
    Definition\Identity,
    Definition\Property as PropertyDefinition,
    Definition\Gateway,
    Definition\Type\StringType,
    Definition\Access,
    Exception\DomainException,
};
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class HttpResourceTest extends TestCase
{
    public function testInterface()
    {
        $resource = HttpResource::of(
            $definition = Definition::rangeable(
                'foobar',
                new Gateway('bar'),
                new Identity('foo'),
                Set::of(
                    PropertyDefinition::class,
                    PropertyDefinition::optional(
                        'foo',
                        new StringType,
                        new Access(Access::READ)
                    )
                )
            ),
            $property = new Property('foo', 42)
        );

        $this->assertInstanceOf(HttpResourceInterface::class, $resource);
        $this->assertSame($definition, $resource->definition());
        $this->assertTrue($resource->has('foo'));
        $this->assertFalse($resource->has('bar'));
        $this->assertSame($property, $resource->property('foo'));
    }

    public function testThrowWhenBuildingWithUndefinedProperty()
    {
        $this->expectException(DomainException::class);

        HttpResource::of(
            Definition::rangeable(
                'foobar',
                new Gateway('bar'),
                new Identity('foo'),
                new Set(PropertyDefinition::class)
            ),
            new Property('foo', 42)
        );
    }
}
