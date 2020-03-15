<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type\FloatType,
    Definition\Type,
    Exception\NormalizationException,
    Exception\DenormalizationException,
};
use PHPUnit\Framework\TestCase;

class FloatTypeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Type::class, new FloatType);
        $this->assertSame('float', (new FloatType)->toString());
    }

    public function testDenormalize()
    {
        $this->assertSame(
            42.0,
            (new FloatType)->denormalize('42.0')
        );
    }

    public function testThrowWhenNotDenormalizingAFloat()
    {
        $this->expectException(DenormalizationException::class);
        $this->expectExceptionMessage('The value must be a float');

        (new FloatType)->denormalize(new \stdClass);
    }

    public function testNormalize()
    {
        $this->assertSame(
            42.0,
            (new FloatType)->normalize('42.0')
        );
    }

    public function testThrowWhenNotNormalizingAFloat()
    {
        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('The value must be a float');

        (new FloatType)->normalize(new \stdClass);
    }
}
