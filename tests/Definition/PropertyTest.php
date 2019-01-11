<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Definition;

use Innmind\Rest\Server\Definition\{
    Property,
    Type\StringType,
    Access,
};
use Innmind\Immutable\Set;
use PHPUnit\Framework\TestCase;

class PropertyTest extends TestCase
{
    public function testInterface()
    {
        $property = Property::optional(
            'foo',
            $type = new StringType,
            new Access(Access::READ),
            'bar'
        );

        $this->assertSame('foo', (string) $property);
        $this->assertSame('foo', $property->name());
        $this->assertSame($type, $property->type());
        $this->assertTrue($property->access()->isReadable());
        $this->assertFalse($property->access()->isCreatable());
        $this->assertFalse($property->access()->isUpdatable());
        $this->assertSame(['bar'], $property->variants()->toPrimitive());
        $this->assertTrue($property->isOptional());
    }
}
