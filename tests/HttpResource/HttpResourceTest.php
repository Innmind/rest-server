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
};
use Innmind\Immutable\{
    Map,
    Set,
};
use PHPUnit\Framework\TestCase;

class HttpResourceTest extends TestCase
{
    public function testInterface()
    {
        $resource = new HttpResource(
            $definition = new Definition(
                'foobar',
                new Identity('foo'),
                Map::of('string', PropertyDefinition::class)
                    (
                        'foo',
                        new PropertyDefinition(
                            'foo',
                            new StringType,
                            new Access(Access::READ),
                            new Set('string'),
                            true
                        )
                    ),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('bar'),
                true,
                new Map('string', 'string')
            ),
            $properties = Map::of('string', Property::class)
                ('foo', $property = new Property('foo', 42))
        );

        $this->assertInstanceOf(HttpResourceInterface::class, $resource);
        $this->assertSame($definition, $resource->definition());
        $this->assertTrue($resource->has('foo'));
        $this->assertFalse($resource->has('bar'));
        $this->assertSame($property, $resource->property('foo'));
        $this->assertSame($properties, $resource->properties());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\DomainException
     */
    public function testThrowWhenBuildingWithUndefinedProperty()
    {
        new HttpResource(
            new Definition(
                'foobar',
                new Identity('foo'),
                new Map('string', PropertyDefinition::class),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('bar'),
                true,
                new Map('string', 'string')
            ),
            Map::of('string', Property::class)
                ('foo', new Property('foo', 42))
        );
    }
}
