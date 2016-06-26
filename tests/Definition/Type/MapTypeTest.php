<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\MapType,
    TypeInterface,
    Types
};
use Innmind\Immutable\{
    Collection,
    MapInterface,
    Map
};

class MapTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(TypeInterface::class, new MapType);
        $this->assertSame(
            ['map'],
            MapType::identifiers()->toPrimitive()
        );
        $this->assertInstanceOf(
            MapType::class,
            MapType::fromConfig(new Collection([
                'inner' => 'string',
                'key' => 'int',
                '_types' => new Types,
            ]))
        );
    }

    public function testDenormalize()
    {
        $t = MapType::fromConfig(
            new Collection([
                'inner' => 'string',
                'key' => 'int',
                '_types' => new Types,
            ])
        );
        $this->assertInstanceOf(MapInterface::class, $t->denormalize(['foo']));
        $this->assertSame('foo', $t->denormalize(['1' => 'foo'])->get(1));
        $this->assertSame(
            'foo',
            MapType::fromConfig(
                new Collection([
                    'inner' => 'string',
                    'key' => 'int',
                    '_types' => new Types,
                ])
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
            new Collection([
                'inner' => 'string',
                'key' => 'int',
                '_types' => new Types,
            ])
        ))
            ->denormalize(new \stdClass);
    }

    public function testNormalize()
    {
        $this->assertSame(
            [1 => 'foo'],
            (MapType::fromConfig(
                new Collection([
                    'inner' => 'string',
                    'key' => 'int',
                    '_types' => new Types,
                ])
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
            new Collection([
                'inner' => 'string',
                'key' => 'int',
                '_types' => new Types,
            ])
        ))
            ->normalize(new \stdClass);
    }
}
