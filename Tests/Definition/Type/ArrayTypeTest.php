<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\ArrayType,
    TypeInterface,
    Types
};
use Innmind\Immutable\{
    Collection,
    SetInterface,
    Set
};

class ArrayTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(TypeInterface::class, new ArrayType);
        $this->assertSame(
            ['array'],
            ArrayType::identifiers()->toPrimitive()
        );
        $this->assertInstanceOf(
            ArrayType::class,
            ArrayType::fromConfig(new Collection([
                'inner' => 'string',
                '_types' => new Types,
            ]))
        );
    }

    public function testDenormalize()
    {
        $this->assertSame(
            ['foo'],
            ArrayType::fromConfig(
                new Collection([
                    'inner' => 'string',
                    '_types' => new Types,
                    'use_set' => false,
                ])
            )
                ->denormalize([new class {
                    public function __toString()
                    {
                        return 'foo';
                    }
                }])
        );
        $t = ArrayType::fromConfig(
            new Collection([
                'inner' => 'string',
                '_types' => new Types,
            ])
        );
        $this->assertInstanceOf(SetInterface::class, $t->denormalize(['foo']));
        $this->assertSame(['foo'], $t->denormalize(['foo'])->toPrimitive());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\DenormalizationException
     * @expectedExceptionMessage The value must be an array of string
     */
    public function testThrowWhenNotDenormalizingAnArray()
    {
        (ArrayType::fromConfig(
            new Collection([
                'inner' => 'string',
                '_types' => new Types,
            ])
        ))
            ->denormalize(new \stdClass);
    }

    public function testNormalize()
    {
        $this->assertSame(
            ['foo'],
            (ArrayType::fromConfig(
                new Collection([
                    'inner' => 'string',
                    '_types' => new Types,
                ])
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
     * @expectedExceptionMessage The value must be traversable
     */
    public function testThrowWhenNotNormalizingAnArray()
    {
        (ArrayType::fromConfig(
            new Collection([
                'inner' => 'string',
                '_types' => new Types,
            ])
        ))
            ->normalize(new \stdClass);
    }
}
