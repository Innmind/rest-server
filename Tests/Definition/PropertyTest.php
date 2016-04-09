<?php
declare(strict_types = 1);

namespace Innmind\Rest\Server\Tests\Definition;

use Innmind\Rest\Server\Definition\{
    Property,
    Type\StringType,
    Access
};
use Innmind\Immutable\Set;

class PropertyTest extends \PHPUnit_Framework_TestCase
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
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
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
