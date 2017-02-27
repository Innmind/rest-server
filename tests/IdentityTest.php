<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server;

use Innmind\Rest\Server\{
    Identity,
    IdentityInterface
};
use PHPUnit\Framework\TestCase;

class IdentityTest extends TestCase
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
