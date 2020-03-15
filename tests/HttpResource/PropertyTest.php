<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\HttpResource;

use Innmind\Rest\Server\HttpResource\Property;
use PHPUnit\Framework\TestCase;

class PropertyTest extends TestCase
{
    public function testInterface()
    {
        $property = new Property('foo', 42);

        $this->assertSame('foo', $property->name());
        $this->assertSame('foo', $property->toString());
        $this->assertSame(42, $property->value());
    }
}
