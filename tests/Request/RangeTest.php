<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Request;

use Innmind\Rest\Server\Request\Range;
use PHPUnit\Framework\TestCase;

class RangeTest extends TestCase
{
    public function testInterface()
    {
        $range = new Range(0, 42);

        $this->assertSame(0, $range->firstPosition());
        $this->assertSame(42, $range->lastPosition());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenFirstPositionNegative()
    {
        new Range(-1, 42);
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenLastPositionLowerThanFirstOne()
    {
        new Range(42, 10);
    }
}
