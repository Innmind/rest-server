<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type\MapType,
    Definition\Type\StringType,
    Definition\Type\IntType,
    Definition\Type\DateType,
    Definition\Type,
    Exception\NormalizationException,
    Exception\DenormalizationException,
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class MapTypeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Type::class, new MapType(
            'string',
            'string',
            new StringType,
            new StringType
        ));
        $this->assertSame(
            'map<int, string>',
            (new MapType(
                'int',
                'string',
                new IntType,
                new StringType
            ))->toString(),
        );
        $this->assertSame(
            'map<int, date<c>>',
            (new MapType(
                'int',
                \DateTimeImmutable::class,
                new IntType,
                new DateType('c')
            ))->toString(),
        );
    }

    public function testDenormalize()
    {
        $type = new MapType(
            'int',
            'string',
            new IntType,
            new StringType
        );
        $this->assertInstanceOf(Map::class, $type->denormalize(['foo']));
        $this->assertSame('foo', $type->denormalize(['1' => 'foo'])->get(1));
        $this->assertSame(
            'foo',
            (new MapType(
                'int',
                'string',
                new IntType,
                new StringType
            ))
                ->denormalize(['1' => new class {
                    public function __toString()
                    {
                        return 'foo';
                    }
                }])
                ->get(1)
        );
    }

    public function testThrowWhenNotDenormalizingAnArray()
    {
        $this->expectException(DenormalizationException::class);
        $this->expectExceptionMessage('The value must be an array of int mapped to string');

        (new MapType(
            'int',
            'string',
            new IntType,
            new StringType
        ))
            ->denormalize(new \stdClass);
    }

    public function testNormalize()
    {
        $this->assertSame(
            [1 => 'foo'],
            (new MapType(
                'int',
                'string',
                new IntType,
                new StringType
            ))
                ->normalize(
                    Map::of('string', 'object')
                        ('1', new class {
                            public function __toString()
                            {
                                return 'foo';
                            }
                        })
                )
        );
    }

    public function testThrowWhenNotNormalizingAnArray()
    {
        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('The value must be a map');

        (new MapType(
            'int',
            'string',
            new IntType,
            new StringType
        ))
            ->normalize(new \stdClass);
    }
}
