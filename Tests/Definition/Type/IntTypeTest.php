<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\IntType,
    TypeInterface
};
use Innmind\Immutable\Collection;

class IntTypeTest extends \PHPUnit_Framework_TestCase
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
            IntType::fromConfig(new Collection([]))
        );
    }

    public function testDenormalize()
    {
        $this->assertSame(
            42,
            (new IntType)->denormalize(42)
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\DenormalizationException
     * @expectedExceptionMessage The value must be an integer
     */
    public function testThrowWhenNotDenormalizingAString()
    {
        (new IntType)->denormalize('42');
    }

    public function testNormalize()
    {
        $this->assertSame(
            42,
            (new IntType)->normalize(42)
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\NormalizationException
     * @expectedExceptionMessage The value must be an integer
     */
    public function testThrowWhenNotNormalizingAString()
    {
        (new IntType)->normalize('42');
    }
}
