<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\{
    Serializer\Normalizer\HttpResourceNormalizer,
    HttpResource\HttpResource,
    HttpResource as HttpResourceInterface,
    HttpResource\Property,
    Definition\HttpResource as ResourceDefinition,
    Definition\Identity,
    Definition\Property as PropertyDefinition,
    Definition\Gateway,
    Definition\Access,
    Definition\Type\StringType,
    Exception\DenormalizationException,
    Exception\NormalizationException,
    Exception\HttpResourceDenormalizationException,
    Exception\HttpResourceNormalizationException,
};
use Innmind\Immutable\{
    Map,
    Set,
};
use PHPUnit\Framework\TestCase;

class HttpResourceNormalizerTest extends TestCase
{
    public function testSupportsDenormalization()
    {
        $normalizer = new HttpResourceNormalizer;

        $this->assertTrue($normalizer->supportsDenormalization(
            ['resource' => []],
            HttpResource::class
        ));
        $this->assertFalse($normalizer->supportsDenormalization(
            [],
            HttpResource::class
        ));
        $this->assertFalse($normalizer->supportsDenormalization(
            null,
            HttpResource::class
        ));
        $this->assertFalse($normalizer->supportsDenormalization(
            ['resource' => []],
            HttpResourceInterface::class
        ));
    }

    public function testDenormalize()
    {
        $normalizer = new HttpResourceNormalizer;
        $def = new ResourceDefinition(
            'foobar',
            new Identity('foo'),
            (new Map('string', PropertyDefinition::class))
                ->put(
                    'bar',
                    new PropertyDefinition(
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

        $resource = $normalizer->denormalize(
            [
                'resource' => [
                    'bar' => 'some content',
                ],
            ],
            HttpResource::class,
            null,
            [
                'definition' => $def,
                'mask' => new Access(Access::CREATE),
            ]
        );

        $this->assertInstanceOf(HttpResource::class, $resource);
        $this->assertSame('some content', $resource->property('bar')->value());
        $this->assertSame(1, $resource->properties()->size());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\BadMethodCallException
     * @expectedExceptionMessage You must give a resource definition
     */
    public function testThrowWhenTryingToDenormalizeWithoutADefinition()
    {
        (new HttpResourceNormalizer)->denormalize(
            ['resource' => []],
            HttpResource::class
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\BadMethodCallException
     * @expectedExceptionMessage You must give an access mask
     */
    public function testThrowWhenTryingToDenormalizeWithoutAMask()
    {
        (new HttpResourceNormalizer)->denormalize(
            ['resource' => []],
            HttpResource::class,
            null,
            [
                'definition' => new ResourceDefinition(
                    'foobar',
                    new Identity('foo'),
                    new Map('string', PropertyDefinition::class),
                    new Map('scalar', 'variable'),
                    new Map('scalar', 'variable'),
                    new Gateway('bar'),
                    true,
                    new Map('string', 'string')
                ),
            ]
        );
    }

    public function testThrowWhenDenormalizationFail()
    {
        $normalizer = new HttpResourceNormalizer;
        $def = new ResourceDefinition(
            'foobar',
            new Identity('foo'),
            (new Map('string', PropertyDefinition::class))
                ->put(
                    'bar',
                    new PropertyDefinition(
                        'bar',
                        new StringType,
                        new Access(Access::READ, Access::CREATE),
                        new Set('string'),
                        false
                    )
                )
                ->put(
                    'baz',
                    new PropertyDefinition(
                        'baz',
                        new StringType,
                        new Access(Access::READ, Access::CREATE),
                        new Set('string'),
                        false
                    )
                )
                ->put(
                    'foo',
                    new PropertyDefinition(
                        'foo',
                        new StringType,
                        new Access(Access::READ),
                        new Set('string'),
                        false
                    )
                )
                ->put(
                    'foobar',
                    new PropertyDefinition(
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
            $normalizer->denormalize(
                [
                    'resource' => [
                        'baz' => ['foo'],
                        'foo' => 'foo',
                    ],
                ],
                HttpResource::class,
                null,
                [
                    'definition' => $def,
                    'mask' => new Access(Access::CREATE),
                ]
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

    public function testSupportsNormalization()
    {
        $normalizer = new HttpResourceNormalizer;

        $this->assertTrue($normalizer->supportsNormalization(
            new HttpResource(
                new ResourceDefinition(
                    'foobar',
                    new Identity('foo'),
                    new Map('string', PropertyDefinition::class),
                    new Map('scalar', 'variable'),
                    new Map('scalar', 'variable'),
                    new Gateway('bar'),
                    true,
                    new Map('string', 'string')
                ),
                new Map('string', Property::class)
            )
        ));
        $this->assertFalse($normalizer->supportsNormalization([]));
    }

    public function testNormalize()
    {
        $def = new ResourceDefinition(
            'foobar',
            new Identity('foo'),
            (new Map('string', PropertyDefinition::class))
                ->put(
                    'bar',
                    new PropertyDefinition(
                        'bar',
                        new StringType,
                        new Access(Access::READ),
                        (new Set('string')),
                        false
                    )
                )
                ->put(
                    'baz',
                    new PropertyDefinition(
                        'baz',
                        new StringType,
                        new Access(Access::CREATE),
                        (new Set('string')),
                        false
                    )
                ),
            new Map('scalar', 'variable'),
            new Map('scalar', 'variable'),
            new Gateway('bar'),
            true,
            new Map('string', 'string')
        );
        $resource = new HttpResource(
            $def,
            (new Map('string', Property::class))
                ->put('bar', new Property('bar', 'baz'))
                ->put('baz', new Property('baz', 'bar'))
        );

        $data = (new HttpResourceNormalizer)->normalize($resource);

        $this->assertSame(['resource' => ['bar' => 'baz']], $data);
    }

    public function testThrowWhenNormalizationFail()
    {
        $def = new ResourceDefinition(
            'foobar',
            new Identity('foo'),
            (new Map('string', PropertyDefinition::class))
                ->put(
                    'bar',
                    new PropertyDefinition(
                        'bar',
                        new StringType,
                        new Access(Access::READ),
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
        $resource = new HttpResource(
            $def,
            (new Map('string', Property::class))
                ->put('bar', new Property('bar', new \stdClass))
        );

        try {
            (new HttpResourceNormalizer)->normalize($resource);

            $this->fail('It should throw an error');
        } catch (HttpResourceNormalizationException $e) {
            $this->assertSame(
                'The input resource is not normalizable',
                $e->getMessage()
            );
            $this->assertSame('string', (string) $e->errors()->keyType());
            $this->assertSame(
                NormalizationException::class,
                (string) $e->errors()->valueType()
            );
            $this->assertSame(1, $e->errors()->size());
            $this->assertSame(
                'The value must be a string',
                $e->errors()->get('bar')->getMessage()
            );
        }
    }
}
