<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\HttpResource;

use Innmind\Rest\Server\HttpResource\Property;
use PHPUnit\Framework\TestCase;

class PropertyTest extends TestCase
{
    public function testInterface()
    {
        $p = new Property('foo', 42);

        $this->assertSame('foo', $p->name());
        $this->assertSame('foo', (string) $p);
        $this->assertSame(42, $p->value());
    }
}