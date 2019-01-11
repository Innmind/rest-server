<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\ListDelegationBuilder,
    Response\HeaderBuilder\ListBuilder,
    Identity as IdentityInterface,
    Definition\Httpresource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header,
};
use Innmind\Immutable\{
    Set,
    SetInterface,
};
use PHPUnit\Framework\TestCase;

class ListDelegationBuilderTest extends TestCase
{
    public function testInterface()
    {
        $build = new ListDelegationBuilder;

        $this->assertInstanceOf(ListBuilder::class, $build);
        $headers = $build(
            new Set(IdentityInterface::class),
            $this->createMock(ServerRequest::class),
            Httpresource::rangeable(
                'foobar',
                new Gateway('bar'),
                new Identity('foo'),
                new Set(Property::class)
            )
        );
        $this->assertInstanceOf(SetInterface::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
    }

    public function testThrowWhenInvalidIdentities()
    {
        $build = new ListDelegationBuilder;

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 must be of type SetInterface<Innmind\Rest\Server\Identity>');

        $build(
            new Set('object'),
            $this->createMock(ServerRequest::class),
            Httpresource::rangeable(
                'foobar',
                new Gateway('bar'),
                new Identity('foo'),
                new Set(Property::class)
            )
        );
    }

    public function testBuild()
    {
        $build = new ListDelegationBuilder(
            $mock1 = $this->createMock(ListBuilder::class),
            $mock2 = $this->createMock(ListBuilder::class)
        );
        $mock1
            ->method('__invoke')
            ->willReturn(
                Set::of(Header::class, $foo = $this->createMock(Header::class))
            );
        $mock2
            ->method('__invoke')
            ->willReturn(
                Set::of(Header::class, $bar = $this->createMock(Header::class))
            );

        $headers = $build(
            new Set(IdentityInterface::class),
            $this->createMock(ServerRequest::class),
            Httpresource::rangeable(
                'foobar',
                new Gateway('bar'),
                new Identity('foo'),
                new Set(Property::class)
            )
        );

        $this->assertSame(
            [$foo, $bar],
            $headers->toPrimitive()
        );
    }
}
