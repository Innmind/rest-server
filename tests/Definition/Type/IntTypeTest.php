<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\IntType,
    TypeInterface,
    Types
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class IntTypeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(TypeInterface::class, new IntType);
        $this->assertSame(
            ['int', 'integer'],
            IntType::identifiers()->toPrimitive()
        );
        $this->assertInstanceOf(
            IntType::class,
            IntType::fromConfig(new Map('scalar', 'variable'), new Types)
        );
        $this->assertSame(
            'int',
            (string) IntType::fromConfig(
                new Map('scalar', 'variable'),
                new Types
            )
        );
    }

    public function testDenormalize()
    {
        $this->assertSame(
            42,
            (new IntType)->denormalize('42')
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\DenormalizationException
     * @expectedExceptionMessage The value must be an integer
     */
    public function testThrowWhenNotDenormalizingAnInt()
    {
        (new IntType)->denormalize(new \stdClass);
    }

    public function testNormalize()
    {
        $this->assertSame(
            42,
            (new IntType)->normalize('42')
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\NormalizationException
     * @expectedExceptionMessage The value must be an integer
     */
    public function testThrowWhenNotNormalizingAnInt()
    {
        (new IntType)->normalize(new \stdClass);
    }
}
