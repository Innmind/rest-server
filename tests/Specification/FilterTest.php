<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Specification;

use Innmind\Rest\Server\{
    Specification\Filter,
    HttpResource,
    HttpResource\Property,
};
use Innmind\Specification\{
    Comparator,
    Sign,
};
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testInterface()
    {
        $filter = new Filter('foo', 'bar');

        $this->assertInstanceOf(Comparator::class, $filter);
        $this->assertSame('foo', $filter->property());
        $this->assertSame('bar', $filter->value());
        $this->assertTrue($filter->sign()->equals(Sign::equality()));
        $resource = $this->createMock(HttpResource::class);
        $resource
            ->method('property')
            ->willReturn(new Property('foo', 'bar'));

        $this->assertTrue($filter->isSatisfiedBy($resource));
    }
}
