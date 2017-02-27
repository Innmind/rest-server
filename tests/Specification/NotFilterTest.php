<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Specification;

use Innmind\Rest\Server\{
    Specification\NotFilter,
    Specification\Filter,
    HttpResourceInterface,
    Property
};
use Innmind\Specification\NotInterface;
use PHPUnit\Framework\TestCase;

class NotFilterTest extends TestCase
{
    public function testInterface()
    {
        $not = new NotFilter(
            $spec = new Filter('foo', 'bar')
        );
        $resource = $this->createMock(HttpResourceInterface::class);
        $resource
            ->method('property')
            ->willReturn(new Property('foo', 'bar'));

        $this->assertInstanceOf(NotInterface::class, $not);
        $this->assertFalse($not->isSatisfiedBy($resource));
        $this->assertSame($spec, $not->specification());
    }
}
