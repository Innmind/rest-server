<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\{
    Serializer\Normalizer\HttpResourceNormalizer,
    HttpResource,
    HttpResourceInterface,
    Property,
    Definition\HttpResource as ResourceDefinition,
    Definition\Identity,
    Definition\Property as PropertyDefinition,
    Definition\Gateway,
    Definition\Access,
    Definition\Type\StringType,
    Exception\DenormalizationException,
    Exception\NormalizationException,
    Exception\HttpResourceDenormalizationException,
    Exception\HttpResourceNormalizationException
};
use Innmind\Immutable\{
    Map,
    Collection,
    Set
};

class HttpResourceNormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportsDenormalization()
    {
        $n = new HttpResourceNormalizer;

        $this->assertTrue($n->supportsDenormalization(
            ['resource' => []],
            HttpResource::class
        ));
        $this->assertFalse($n->supportsDenormalization(
            [],
            HttpResource::class
        ));
        $this->assertFalse($n->supportsDenormalization(
            null,
            HttpResource::class
        ));
        $this->assertFalse($n->supportsDenormalization(
            ['resource' => []],
            HttpResourceInterface::class
        ));
    }

    public function testDenormalize()
    {
        $n = new HttpResourceNormalizer;
        $def = new ResourceDefinition(
            'foobar',
            new Identity('foo'),
            (new Map('string', PropertyDefinition::class))
                ->put(
                    'bar',
                    new PropertyDefinition(
                        'bar',
                        new StringType,
                        new Access(
                            (new Set('string'))
                                ->add(Access::READ)
                                ->add(Access::CREATE)
                        ),
                        (new Set('string'))->add('baz'),
                        false
                    )
                ),
            new Collection([]),
            new Collection([]),
            new Gateway('bar'),
            true
        );

        $r = $n->denormalize(
            [
                'resource' => [
                    'bar' => 'some content',
                ],
            ],
            HttpResource::class,
            null,
            [
                'definition' => $def,
                'mask' => new Access((new Set('string'))->add(Access::CREATE)),
            ]
        );

        $this->assertInstanceOf(HttpResource::class, $r);
        $this->assertSame('some content', $r->property('bar')->value());
        $this->assertSame(1, $r->properties()->size());
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
                    new Collection([]),
                    new Collection([]),
                    new Gateway('bar'),
                    true
                ),
            ]
        );
    }

    public function testThrowWhenDenormalizationFail()
    {
        $n = new HttpResourceNormalizer;
        $def = new ResourceDefinition(
            'foobar',
            new Identity('foo'),
            (new Map('string', PropertyDefinition::class))
                ->put(
                    'bar',
                    new PropertyDefinition(
                        'bar',
                        new StringType,
                        new Access(
                            (new Set('string'))->add(Access::READ)
                        ),
                        new Set('string'),
                        false
                    )
                )
                ->put(
                    'baz',
                    new PropertyDefinition(
                        'baz',
                        new StringType,
                        new Access(
                            (new Set('string'))
                                ->add(Access::READ)
                                ->add(Access::CREATE)
                        ),
                        new Set('string'),
                        false
                    )
                )
                ->put(
                    'foo',
                    new PropertyDefinition(
                        'foo',
                        new StringType,
                        new Access(
                            (new Set('string'))->add(Access::READ)
                        ),
                        new Set('string'),
                        false
                    )
                ),
            new Collection([]),
            new Collection([]),
            new Gateway('bar'),
            true
        );

        try {
            $n->denormalize(
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
                    'mask' => new Access((new Set('string'))->add(Access::CREATE)),
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
        $n = new HttpResourceNormalizer;

        $this->assertTrue($n->supportsNormalization(
            new HttpResource(
                new ResourceDefinition(
                    'foobar',
                    new Identity('foo'),
                    new Map('string', PropertyDefinition::class),
                    new Collection([]),
                    new Collection([]),
                    new Gateway('bar'),
                    true
                ),
                new Map('string', Property::class)
            )
        ));
        $this->assertFalse($n->supportsNormalization([]));
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
                        new Access(
                            (new Set('string'))->add(Access::READ)
                        ),
                        (new Set('string')),
                        false
                    )
                )
                ->put(
                    'baz',
                    new PropertyDefinition(
                        'baz',
                        new StringType,
                        new Access(
                            (new Set('string'))->add(Access::CREATE)
                        ),
                        (new Set('string')),
                        false
                    )
                ),
            new Collection([]),
            new Collection([]),
            new Gateway('bar'),
            true
        );
        $r = new HttpResource(
            $def,
            (new Map('string', Property::class))
                ->put('bar', new Property('bar', 'baz'))
                ->put('baz', new Property('baz', 'bar'))
        );

        $d = (new HttpResourceNormalizer)->normalize($r);

        $this->assertSame(['resource' => ['bar' => 'baz']], $d);
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
                        new Access(
                            (new Set('string'))->add(Access::READ)
                        ),
                        (new Set('string'))->add('baz'),
                        false
                    )
                ),
            new Collection([]),
            new Collection([]),
            new Gateway('bar'),
            true
        );
        $r = new HttpResource(
            $def,
            (new Map('string', Property::class))
                ->put('bar', new Property('bar', new \stdClass))
        );

        try {
            (new HttpResourceNormalizer)->normalize($r);

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
