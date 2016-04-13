<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\SetType,
    TypeInterface,
    Types
};
use Innmind\Immutable\{
    Collection,
    SetInterface,
    Set
};

class SetTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(TypeInterface::class, new SetType);
        $this->assertSame(
            ['set'],
            SetType::identifiers()->toPrimitive()
        );
        $this->assertInstanceOf(
            SetType::class,
            SetType::fromConfig(new Collection([
                'inner' => 'string',
                '_types' => new Types,
            ]))
        );
    }

    public function testDenormalize()
    {
        $t = SetType::fromConfig(
            new Collection([
                'inner' => 'string',
                '_types' => new Types,
            ])
        );
        $this->assertInstanceOf(SetInterface::class, $t->denormalize(['foo']));
        $this->assertSame(['foo'], $t->denormalize(['foo'])->toPrimitive());
        $this->assertSame(
            ['foo'],
            SetType::fromConfig(
                new Collection([
                    'inner' => 'string',
                    '_types' => new Types,
                ])
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
            (SetType::fromConfig(
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
     * @expectedExceptionMessage The value must be a set
     */
    public function testThrowWhenNotNormalizingAnArray()
    {
        (SetType::fromConfig(
            new Collection([
                'inner' => 'string',
                '_types' => new Types,
            ])
        ))
            ->normalize(new \stdClass);
    }
}
