<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Specification;

use Innmind\Rest\Server\{
    Specification\NotFilter,
    Specification\Filter,
    HttpResource,
    HttpResource\Property,
};
use Innmind\Specification\Not;
use PHPUnit\Framework\TestCase;

class NotFilterTest extends TestCase
{
    public function testInterface()
    {
        $not = new NotFilter(
            $spec = new Filter('foo', 'bar')
        );
        $resource = $this->createMock(HttpResource::class);
        $resource
            ->method('property')
            ->willReturn(new Property('foo', 'bar'));

        $this->assertInstanceOf(Not::class, $not);
        $this->assertFalse($not->isSatisfiedBy($resource));
        $this->assertSame($spec, $not->specification());
    }
}
