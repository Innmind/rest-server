<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests\Definition\Type;

use Innmind\Rest\Server\Definition\{
    Type\BoolType,
    TypeInterface
};
use Innmind\Immutable\Collection;

class BoolTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(TypeInterface::class, new BoolType);
        $this->assertSame(
            ['bool', 'boolean'],
            BoolType::identifiers()->toPrimitive()
        );
        $this->assertInstanceOf(
            BoolType::class,
            BoolType::fromConfig(new Collection([]))
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
