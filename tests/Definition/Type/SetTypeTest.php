<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\SetType,
    Type\StringType,
    Type\DateType,
    Type,
};
use Innmind\Immutable\{
    SetInterface,
    Set,
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
            'set<string>',
            (string) new SetType(
                'string',
                new StringType
            )
        );
        $this->assertSame(
            'set<date<c>>',
            (string) new SetType(
                \DateTimeImmutable::class,
                new DateType('c')
            )
        );
    }

    public function testDenormalize()
    {
        $type = new SetType(
            'string',
            new StringType
        );
        $this->assertInstanceOf(SetInterface::class, $type->denormalize(['foo']));
        $this->assertSame(['foo'], $type->denormalize(['foo'])->toPrimitive());
        $this->assertSame(
            ['foo'],
            (new SetType(
                'string',
                new StringType
            ))
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
        (new SetType(
            'string',
            new StringType
        ))
            ->denormalize(new \stdClass);
    }

    public function testNormalize()
    {
        $this->assertSame(
            ['foo'],
            (new SetType(
                'string',
                new StringType
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
        (new SetType(
            'string',
            new StringType
        ))
            ->normalize(new \stdClass);
    }
}
