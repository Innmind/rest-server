<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\Property;

class PropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $p = new Property('foo', 42);

        $this->assertSame('foo', $p->name());
        $this->assertSame('foo', (string) $p);
        $this->assertSame(42, $p->value());
    }
}
