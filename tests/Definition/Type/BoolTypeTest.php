<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\BoolType,
    Type,
    Types
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class BoolTypeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Type::class, new BoolType);
        $this->assertSame(
            ['bool', 'boolean'],
            BoolType::identifiers()->toPrimitive()
        );
        $this->assertInstanceOf(
            BoolType::class,
            BoolType::fromConfig(new Map('scalar', 'variable'), new Types)
        );
        $this->assertSame(
            'bool',
            (string) BoolType::fromConfig(
                new Map('scalar', 'variable'),
                new Types
            )
        );
    }

    public function testDenormalize()
    {
        $this->assertSame(
            true,
            (new BoolType)->denormalize('42.0')
        );
    }

    public function testNormalize()
    {
        $this->assertSame(
            false,
            (new BoolType)->normalize('0')
        );
    }
}
