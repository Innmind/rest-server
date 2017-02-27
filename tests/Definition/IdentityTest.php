<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Definition\Identity;
use PHPUnit\Framework\TestCase;

class IdentityTest extends TestCase
{
    public function testInterface()
    {
        $i = new Identity('foo');

        $this->assertSame('foo', $i->property());
        $this->assertSame('foo', (string) $i);
    }
}
