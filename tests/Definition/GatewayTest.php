<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Definition\Gateway;
use PHPUnit\Framework\TestCase;

class GatewayTest extends TestCase
{
    public function testInterface()
    {
        $g = new Gateway('foo');

        $this->assertSame('foo', $g->name());
        $this->assertSame('foo', (string) $g);
    }
}
