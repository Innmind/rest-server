<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\MapType,
    Type\StringType,
    Type\IntType,
    Type\DateType,
    Type,
};
use Innmind\Immutable\{
    MapInterface,
    Map,
};
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
            (string) new MapType(
                'int',
                'string',
                new IntType,
                new StringType
            )
        );
        $this->assertSame(
            'map<int, date<c>>',
            (string) new MapType(
                'int',
                \DateTimeImmutable::class,
                new IntType,
                new DateType('c')
            )
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
        $this->assertInstanceOf(MapInterface::class, $type->denormalize(['foo']));
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

    /**
     * @expectedException Innmind\Rest\Server\Exception\DenormalizationException
     * @expectedExceptionMessage The value must be an array of int mapped to string
     */
    public function testThrowWhenNotDenormalizingAnArray()
    {
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

    /**
     * @expectedException Innmind\Rest\Server\Exception\NormalizationException
     * @expectedExceptionMessage The value must be a map
     */
    public function testThrowWhenNotNormalizingAnArray()
    {
        (new MapType(
            'int',
            'string',
            new IntType,
            new StringType
        ))
            ->normalize(new \stdClass);
    }
}
