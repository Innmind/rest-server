<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\{
    Definition\Type\IntType,
    Definition\Type,
    Exception\NormalizationException,
    Exception\DenormalizationException,
};
use PHPUnit\Framework\TestCase;

class IntTypeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Type::class, new IntType);
        $this->assertSame('int', (new IntType)->toString());
    }

    public function testDenormalize()
    {
        $this->assertSame(
            42,
            (new IntType)->denormalize('42')
        );
    }

    public function testThrowWhenNotDenormalizingAnInt()
    {
        $this->expectException(DenormalizationException::class);
        $this->expectExceptionMessage('The value must be an integer');

        (new IntType)->denormalize(new \stdClass);
    }

    public function testNormalize()
    {
        $this->assertSame(
            42,
            (new IntType)->normalize('42')
        );
    }

    public function testThrowWhenNotNormalizingAnInt()
    {
        $this->expectException(NormalizationException::class);
        $this->expectExceptionMessage('The value must be an integer');

        (new IntType)->normalize(new \stdClass);
    }
}
