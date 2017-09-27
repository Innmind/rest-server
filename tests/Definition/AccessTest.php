<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Definition\Access;
use Innmind\Immutable\SetInterface;
use PHPUnit\Framework\TestCase;

class AccessTest extends TestCase
{
    public function testIsReadable()
    {
        $this->assertTrue(
            (new Access(Access::READ))->isReadable()
        );
        $this->assertTrue(
            (new Access(
                Access::READ,
                Access::CREATE,
                Access::UPDATE
            ))
                ->isReadable()
        );
    }

    public function testIsCreatable()
    {
        $this->assertTrue(
            (new Access(Access::CREATE))->isCreatable()
        );
        $this->assertTrue(
            (new Access(
                Access::READ,
                Access::CREATE,
                Access::UPDATE
            ))
                ->isCreatable()
        );
    }

    public function testIsUpdatable()
    {
        $this->assertTrue(
            (new Access(Access::UPDATE))->isUpdatable()
        );
        $this->assertTrue(
            (new Access(
                Access::READ,
                Access::CREATE,
                Access::UPDATE
            ))
                ->isUpdatable()
        );
    }

    public function testMask()
    {
        $a = (new Access(
            Access::READ,
            Access::CREATE,
            Access::UPDATE
        ));

        $this->assertInstanceOf(SetInterface::class, $a->mask());
        $this->assertSame('string', (string) $a->mask()->type());
        $this->assertSame(
            [Access::READ, Access::CREATE, Access::UPDATE],
            $a->mask()->toPrimitive()
        );
    }

    public function testMatches()
    {
        $a = (new Access(
            Access::READ,
            Access::CREATE,
            Access::UPDATE
        ));
        $this->assertTrue($a->matches(
            new Access(Access::READ)
        ));
        $this->assertTrue($a->matches(
            new Access(Access::CREATE)
        ));
        $this->assertTrue($a->matches(
            new Access(Access::UPDATE)
        ));

        $a = new Access(Access::READ);
        $this->assertTrue($a->matches(
            new Access(Access::READ)
        ));
        $this->assertFalse($a->matches(
            new Access(Access::CREATE)
        ));
        $this->assertFalse($a->matches(
            new Access(Access::UPDATE)
        ));
    }
}
