<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\FloatType,
    TypeInterface
};
use Innmind\Immutable\Collection;

class FloatTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(TypeInterface::class, new FloatType);
        $this->assertSame(
            ['float'],
            FloatType::identifiers()->toPrimitive()
        );
        $this->assertInstanceOf(
            FloatType::class,
            FloatType::fromConfig(new Collection([]))
        );
    }

    public function testDenormalize()
    {
        $this->assertSame(
            42.0,
            (new FloatType)->denormalize('42.0')
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\DenormalizationException
     * @expectedExceptionMessage The value must be a float
     */
    public function testThrowWhenNotDenormalizingAString()
    {
        (new FloatType)->denormalize(new \stdClass);
    }

    public function testNormalize()
    {
        $this->assertSame(
            42.0,
            (new FloatType)->normalize('42.0')
        );
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\NormalizationException
     * @expectedExceptionMessage The value must be a float
     */
    public function testThrowWhenNotNormalizingAString()
    {
        (new FloatType)->normalize(new \stdClass);
    }
}
