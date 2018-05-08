<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\FloatType,
    Type,
    Types,
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class FloatTypeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Type::class, new FloatType);
        $this->assertSame(
            ['float'],
            FloatType::identifiers()->toPrimitive()
        );
        $this->assertInstanceOf(
            FloatType::class,
            FloatType::fromConfig(new Map('scalar', 'variable'), new Types)
        );
        $this->assertSame(
            'float',
            (string) FloatType::fromConfig(
                new Map('scalar', 'variable'),
                new Types
            )
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
    public function testThrowWhenNotDenormalizingAFloat()
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
    public function testThrowWhenNotNormalizingAFloat()
    {
        (new FloatType)->normalize(new \stdClass);
    }
}
