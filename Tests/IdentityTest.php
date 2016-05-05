<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests;

use Innmind\Rest\Server\{
    Identity,
    IdentityInterface
};

class IdentityTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $i = new Identity(42);

        $this->assertInstanceOf(IdentityInterface::class, $i);
        $this->assertSame(42, $i->value());
        $this->assertSame('42', (string) $i);
    }

    public function testThrowWhenInvalidData()
    {
        try {
            new Identity(new \sdtClass);
            $this->fail('It should throw a type error');
        } catch (\Throwable $e) {
            $this->assertTrue(true);
        }
    }
}
