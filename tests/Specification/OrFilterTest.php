<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Specification;

use Innmind\Rest\Server\{
    Specification\OrFilter,
    Specification\Filter,
    HttpResourceInterface,
    Property
};
use Innmind\Specification\CompositeInterface;
use PHPUnit\Framework\TestCase;

class OrFilterTest extends TestCase
{
    public function testInterface()
    {
        $and = new OrFilter(
            $left = new Filter('foo', 'bar'),
            $right = new Filter('bar', 'baz')
        );

        $this->assertInstanceOf(CompositeInterface::class, $and);
        $this->assertSame($left, $and->left());
        $this->assertSame($right, $and->right());
        $this->assertSame('OR', (string) $and->operator());
        $resource = $this->createMock(HttpResourceInterface::class);
        $resource
            ->method('property')
            ->will($this->onConsecutiveCalls(
                new Property('foo', 'bar'),
                new Property('bar', 'baz')
            ));

        $this->assertTrue($and->isSatisfiedBy($resource));
    }
}
