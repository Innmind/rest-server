<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Definition\Gateway;

class GatewayTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $g = new Gateway('foo');

        $this->assertSame('foo', $g->name());
        $this->assertSame('foo', (string) $g);
    }
}
