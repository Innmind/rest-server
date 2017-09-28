<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\MapType,
    Type,
    Types
};
use Innmind\Immutable\{
    MapInterface,
    Map
};
use PHPUnit\Framework\TestCase;

class MapTypeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Type::class, new MapType);
        $this->assertSame(
            ['map'],
            MapType::identifiers()->toPrimitive()
        );
        $this->assertInstanceOf(
            MapType::class,
            MapType::fromConfig(
                (new Map('scalar', 'variable'))
                    ->put('inner', 'string')
                    ->put('key', 'int'),
                new Types
            )
        );
        $this->assertSame(
            'map<int, string>',
            (string) MapType::fromConfig(
                (new Map('scalar', 'variable'))
                    ->put('inner', 'string')
                    ->put('key', 'int'),
                new Types
            )
        );
        $this->assertSame(
            'map<int, date<c>>',
            (string) MapType::fromConfig(
                (new Map('scalar', 'variable'))
                    ->put('inner', 'date')
                    ->put('format', 'c')
                    ->put('key', 'int'),
                new Types
            )
        );
    }

    public function testDenormalize()
    {
        $t = MapType::fromConfig(
            (new Map('scalar', 'variable'))
                ->put('inner', 'string')
                ->put('key', 'int'),
            new Types
        );
        $this->assertInstanceOf(MapInterface::class, $t->denormalize(['foo']));
        $this->assertSame('foo', $t->denormalize(['1' => 'foo'])->get(1));
        $this->assertSame(
            'foo',
            MapType::fromConfig(
                (new Map('scalar', 'variable'))
                    ->put('inner', 'string')
                    ->put('key', 'int'),
                new Types
            )
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
        (MapType::fromConfig(
            (new Map('scalar', 'variable'))
                ->put('inner', 'string')
                ->put('key', 'int'),
            new Types
        ))
            ->denormalize(new \stdClass);
    }

    public function testNormalize()
    {
        $this->assertSame(
            [1 => 'foo'],
            (MapType::fromConfig(
                (new Map('scalar', 'variable'))
                    ->put('inner', 'string')
                    ->put('key', 'int'),
                new Types
            ))
                ->normalize(
                    (new Map('string', 'object'))
                        ->put('1', new class {
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
        (MapType::fromConfig(
            (new Map('scalar', 'variable'))
                ->put('inner', 'string')
                ->put('key', 'int'),
            new Types
        ))
            ->normalize(new \stdClass);
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 must be of type MapInterface<scalar, variable>
     */
    public function testThrowWhenInvalidConfigMap()
    {
        MapType::fromConfig(new Map('string', 'string'), new Types);
    }
}
