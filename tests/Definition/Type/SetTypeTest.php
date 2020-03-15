<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type\SetType,
    Definition\Type\StringType,
    Definition\Type\DateType,
    Definition\Type,
    Exception\NormalizationException,
    Exception\DenormalizationException,
};
use Innmind\Immutable\Set;
use function Innmind\Immutable\unwrap;
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
        $this->assertInstanceOf(Set::class, $type->denormalize(['foo']));
        $this->assertSame(['foo'], unwrap($type->denormalize(['foo'])));
        $this->assertSame(
            ['foo'],
            unwrap(
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
            ),
        );
    }

    public function testThrowWhenNotDenormalizingAnArray()
    {
        $this->expectException(DenormalizationException::class);
        $this->expectExceptionMessage('The value must be an array of string');

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

    public function testThrowWhenNotNormalizingAnArray()
    {
        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('The value must be a set');

        (new SetType(
            'string',
            new StringType
        ))
            ->normalize(new \stdClass);
    }
}
