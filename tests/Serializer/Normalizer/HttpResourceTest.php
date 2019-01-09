<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Serializer\Normalizer;

use Innmind\Rest\Server\{
    Serializer\Normalizer\HttpResource,
    HttpResource\HttpResource as Resource,
    HttpResource\Property,
    Definition\HttpResource as ResourceDefinition,
    Definition\Identity,
    Definition\Property as PropertyDefinition,
    Definition\Gateway,
    Definition\Access,
    Definition\Type\StringType,
    Exception\NormalizationException,
    Exception\HttpResourceNormalizationException,
};
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class HttpResourceTest extends TestCase
{
    public function testNormalize()
    {
        $def = ResourceDefinition::rangeable(
            'foobar',
            new Gateway('bar'),
            new Identity('foo'),
            Set::of(
                PropertyDefinition::class,
                PropertyDefinition::required(
                    'bar',
                    new StringType,
                    new Access(Access::READ)
                ),
                PropertyDefinition::required(
                    'baz',
                    new StringType,
                    new Access(Access::CREATE)
                )
            )
        );
        $resource = Resource::of(
            $def,
            new Property('bar', 'baz'),
            new Property('baz', 'bar')
        );

        $data = (new HttpResource)($resource);

        $this->assertSame(['resource' => ['bar' => 'baz']], $data);
    }

    public function testThrowWhenNormalizationFail()
    {
        $def = ResourceDefinition::rangeable(
            'foobar',
            new Gateway('bar'),
            new Identity('foo'),
            Set::of(
                PropertyDefinition::class,
                PropertyDefinition::required(
                    'bar',
                    new StringType,
                    new Access(Access::READ),
                    'baz'
                )
            )
        );
        $resource = Resource::of(
            $def,
            new Property('bar', new \stdClass)
        );

        try {
            (new HttpResource)($resource);

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
