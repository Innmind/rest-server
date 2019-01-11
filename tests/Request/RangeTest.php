<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Request;

use Innmind\Rest\Server\{
    Request\Range,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;

class RangeTest extends TestCase
{
    public function testInterface()
    {
        $range = new Range(0, 42);

        $this->assertSame(0, $range->firstPosition());
        $this->assertSame(42, $range->lastPosition());
    }

    public function testThrowWhenFirstPositionNegative()
    {
        $this->expectException(DomainException::class);

        new Range(-1, 42);
    }

    public function testThrowWhenLastPositionLowerThanFirstOne()
    {
        $this->expectException(DomainException::class);

        new Range(42, 10);
    }
}
