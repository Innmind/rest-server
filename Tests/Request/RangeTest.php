<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Request;

use Innmind\Rest\Server\Request\Range;

class RangeTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $range = new Range(0, 42);

        $this->assertSame(0, $range->firstPosition());
        $this->assertSame(42, $range->lastPosition());
    }
}
