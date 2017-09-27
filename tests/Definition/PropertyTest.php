<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Definition\{
    Property,
    Type\StringType,
    Access
};
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class PropertyTest extends TestCase
{
    public function testInterface()
    {
        $p = new Property(
            'foo',
            $t = new StringType,
            new Access(
                (new Set('string'))->add(Access::READ)
            ),
            (new Set('string'))->add('bar'),
            true
        );

        $this->assertSame('foo', (string) $p);
        $this->assertSame('foo', $p->name());
        $this->assertSame($t, $p->type());
        $this->assertTrue($p->access()->isReadable());
        $this->assertFalse($p->access()->isCreatable());
        $this->assertFalse($p->access()->isUpdatable());
        $this->assertSame(['bar'], $p->variants()->toPrimitive());
        $this->assertTrue($p->isOptional());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 4 must be of type SetInterface<string>
     */
    public function testThrowWhenGivingInvalidVariants()
    {
        new Property(
            'foo',
            new StringType,
            new Access((new Set('string'))),
            (new Set('int')),
            true
        );
    }
}
