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
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class HttpResourceTest extends TestCase
{
    public function testDenormalize()
    {
        $denormalize = new HttpResource;
        $definition = ResourceDefinition::rangeable(
            'foobar',
            new Gateway('bar'),
            new Identity('foo'),
            Set::of(
                Property::class,
                Property::required(
                    'bar',
                    new StringType,
                    new Access(Access::READ, Access::CREATE),
                    'baz'
                )
            )
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
        $definition = ResourceDefinition::rangeable(
            'foobar',
            new Gateway('bar'),
            new Identity('foo'),
            Set::of(
                Property::class,
                Property::required(
                    'bar',
                    new StringType,
                    new Access(Access::READ, Access::CREATE)
                ),
                Property::required(
                    'baz',
                    new StringType,
                    new Access(Access::READ, Access::CREATE)
                ),
                Property::required(
                    'foo',
                    new StringType,
                    new Access(Access::READ)
                ),
                Property::required(
                    'foobar',
                    new StringType,
                    new Access(Access::READ)
                )
            )
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
