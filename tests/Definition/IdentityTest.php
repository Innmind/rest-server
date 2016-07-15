<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Definition\Identity;

class IdentityTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $i = new Identity('foo');

        $this->assertSame('foo', $i->property());
        $this->assertSame('foo', (string) $i);
    }
}
