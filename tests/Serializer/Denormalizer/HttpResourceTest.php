<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\Denormalizer;

use Innmind\Rest\Server\{
    Serializer\Denormalizer\HttpResource,
    HttpResource\HttpResource as Resource,
    Definition\HttpResource as ResourceDefinition,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway,
    Definition\Access,
    Definition\Type\StringType,
    Exception\DenormalizationException,
    Exception\HttpResourceDenormalizationException,
};
use Innmind\Immutable\{
    Map,
    Set,
};
use PHPUnit\Framework\TestCase;

class HttpResourceTest extends TestCase
{
    public function testDenormalize()
    {
        $denormalize = new HttpResource;
        $definition = new ResourceDefinition(
            'foobar',
            new Identity('foo'),
            Map::of('string', Property::class)
                (
                    'bar',
                    new Property(
                        'bar',
                        new StringType,
                        new Access(Access::READ, Access::CREATE),
                        (new Set('string'))->add('baz'),
                        false
                    )
                ),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Gateway('bar'),
            true,
            new Map('string', 'string')
        );

        $resource = $denormalize(
            [
                'resource' => [
                    'bar' => 'some content',
                ],
            ],
            $definition,
            new Access(Access::CREATE)
        );

        $this->assertInstanceOf(Resource::class, $resource);
        $this->assertSame('some content', $resource->property('bar')->value());
        $this->assertSame(1, $resource->properties()->size());
    }

    public function testThrowWhenDenormalizationFail()
    {
        $denormalize = new HttpResource;
        $definition = new ResourceDefinition(
            'foobar',
            new Identity('foo'),
            Map::of('string', Property::class)
                (
                    'bar',
                    new Property(
                        'bar',
                        new StringType,
                        new Access(Access::READ, Access::CREATE),
                        new Set('string'),
                        false
                    )
                )
                (
                    'baz',
                    new Property(
                        'baz',
                        new StringType,
                        new Access(Access::READ, Access::CREATE),
                        new Set('string'),
                        false
                    )
                )
                (
                    'foo',
                    new Property(
                        'foo',
                        new StringType,
                        new Access(Access::READ),
                        new Set('string'),
                        false
                    )
                )
                (
                    'foobar',
                    new Property(
                        'foobar',
                        new StringType,
                        new Access(Access::READ),
                        new Set('string'),
                        false
                    )
                ),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Gateway('bar'),
            true,
            new Map('string', 'string')
        );

        try {
            $denormalize(
                [
                    'resource' => [
                        'baz' => ['foo'],
                        'foo' => 'foo',
                    ],
                ],
                $definition,
                new Access(Access::CREATE)
            );
            $this->fail('It should throw an exception');
        } catch (HttpResourceDenormalizationException $e) {
            $this->assertSame(
                'The input resource is not denormalizable',
                $e->getMessage()
            );
            $this->assertSame('string', (string) $e->errors()->keyType());
            $this->assertSame(
                DenormalizationException::class,
                (string) $e->errors()->valueType()
            );
            $this->assertSame(3, $e->errors()->size());
            $this->assertSame(
                'The field is missing',
                $e->errors()->get('bar')->getMessage()
            );
            $this->assertSame(
                'The value must be a string',
                $e->errors()->get('baz')->getMessage()
            );
            $this->assertSame(
                'The field is not allowed',
                $e->errors()->get('foo')->getMessage()
            );
        }
    }
}
