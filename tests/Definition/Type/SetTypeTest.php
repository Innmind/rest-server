<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\SetType,
    Type,
    Types
};
use Innmind\Immutable\{
    SetInterface,
    Set,
    Map
};
use PHPUnit\Framework\TestCase;

class SetTypeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Type::class, new SetType);
        $this->assertSame(
            ['set'],
            SetType::identifiers()->toPrimitive()
        );
        $this->assertInstanceOf(
            SetType::class,
            SetType::fromConfig(
                (new Map('scalar', 'variable'))
                    ->put('inner', 'string'),
                new Types
            )
        );
        $this->assertSame(
            'set<string>',
            (string) SetType::fromConfig(
                (new Map('scalar', 'variable'))
                    ->put('inner', 'string'),
                new Types
            )
        );
        $this->assertSame(
            'set<date<c>>',
            (string) SetType::fromConfig(
                (new Map('scalar', 'variable'))
                    ->put('format', 'c')
                    ->put('inner', 'date'),
                new Types
            )
        );
    }

    public function testDenormalize()
    {
        $t = SetType::fromConfig(
            (new Map('scalar', 'variable'))
                ->put('inner', 'string'),
            new Types
        );
        $this->assertInstanceOf(SetInterface::class, $t->denormalize(['foo']));
        $this->assertSame(['foo'], $t->denormalize(['foo'])->toPrimitive());
        $this->assertSame(
            ['foo'],
            SetType::fromConfig(
                (new Map('scalar', 'variable'))
                    ->put('inner', 'string'),
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
            (new Map('scalar', 'variable'))
                ->put('inner', 'string'),
            new Types
        ))
            ->denormalize(new \stdClass);
    }

    public function testNormalize()
    {
        $this->assertSame(
            ['foo'],
            (SetType::fromConfig(
                (new Map('scalar', 'variable'))
                    ->put('inner', 'string'),
                new Types
            ))
                ->normalize(
                    (new Set('object'))
                        ->add(new class {
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
            (new Map('scalar', 'variable'))
                ->put('inner', 'string'),
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
