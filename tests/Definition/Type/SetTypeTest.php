<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\SetType,
    Type\StringType,
    Type,
    Types,
};
use Innmind\Immutable\{
    SetInterface,
    Set,
    Map,
};
use PHPUnit\Framework\TestCase;

class SetTypeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Type::class, new SetType(
            'string',
            new StringType
        ));
        $this->assertSame(
            ['set'],
            SetType::identifiers()->toPrimitive()
        );
        $this->assertInstanceOf(
            SetType::class,
            SetType::fromConfig(
                Map::of('scalar', 'variable')
                    ('inner', 'string'),
                new Types
            )
        );
        $this->assertSame(
            'set<string>',
            (string) SetType::fromConfig(
                Map::of('scalar', 'variable')
                    ('inner', 'string'),
                new Types
            )
        );
        $this->assertSame(
            'set<date<c>>',
            (string) SetType::fromConfig(
                Map::of('scalar', 'variable')
                    ('format', 'c')
                    ('inner', 'date'),
                new Types
            )
        );
    }

    public function testDenormalize()
    {
        $type = SetType::fromConfig(
            Map::of('scalar', 'variable')
                ('inner', 'string'),
            new Types
        );
        $this->assertInstanceOf(SetInterface::class, $type->denormalize(['foo']));
        $this->assertSame(['foo'], $type->denormalize(['foo'])->toPrimitive());
        $this->assertSame(
            ['foo'],
            SetType::fromConfig(
                Map::of('scalar', 'variable')
                    ('inner', 'string'),
                new Types
            )
                ->denormalize([new class {
                    public function __toString()
                    {
                        return 'foo';
                    }
                }])
                ->toPrimitive()
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\DenormalizationException
     * @expectedExceptionMessage The value must be an array of string
     */
    public function testThrowWhenNotDenormalizingAnArray()
    {
        (SetType::fromConfig(
            Map::of('scalar', 'variable')
                ('inner', 'string'),
            new Types
        ))
            ->denormalize(new \stdClass);
    }

    public function testNormalize()
    {
        $this->assertSame(
            ['foo'],
            (SetType::fromConfig(
                Map::of('scalar', 'variable')
                    ('inner', 'string'),
                new Types
            ))
                ->normalize(
                    Set::of('object', new class {
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
     * @expectedExceptionMessage The value must be a set
     */
    public function testThrowWhenNotNormalizingAnArray()
    {
        (SetType::fromConfig(
            Map::of('scalar', 'variable')
                ('inner', 'string'),
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
        SetType::fromConfig(new Map('string', 'string'), new Types);
    }
}
