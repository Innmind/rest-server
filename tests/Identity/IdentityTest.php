<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Identity;

use Innmind\Rest\Server\{
    Identity\Identity,
    Identity as IdentityInterface,
};
use PHPUnit\Framework\TestCase;

class IdentityTest extends TestCase
{
    public function testInterface()
    {
        $identity = new Identity(42);

        $this->assertInstanceOf(IdentityInterface::class, $identity);
        $this->assertSame(42, $identity->value());
        $this->assertSame('42', $identity->toString());
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
