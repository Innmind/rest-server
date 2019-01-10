<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\UnlinkDelegationBuilder,
    Response\HeaderBuilder\UnlinkBuilder,
    Identity\Identity,
    Reference,
    Definition\Httpresource,
    Definition\Identity as IdentityDefinition,
    Definition\Property,
    Definition\Gateway,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header,
};
use Innmind\Immutable\{
    SetInterface,
    Set,
};
use PHPUnit\Framework\TestCase;

class UnlinkDelegationBuilderTest extends TestCase
{
    public function testInterface()
    {
        $build = new UnlinkDelegationBuilder;

        $this->assertInstanceOf(UnlinkBuilder::class, $build);
        $headers = $build(
            $this->createMock(ServerRequest::class),
            new Reference(
                Httpresource::rangeable(
                    'foobar',
                    new Gateway('bar'),
                    new IdentityDefinition('foo'),
                    new Set(Property::class)
                ),
                new Identity('foo')
            )
        );
        $this->assertInstanceOf(SetInterface::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
    }

    public function testBuild()
    {
        $build = new UnlinkDelegationBuilder(
            $mock1 = $this->createMock(UnlinkBuilder::class),
            $mock2 = $this->createMock(UnlinkBuilder::class)
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
            $this->createMock(ServerRequest::class),
            new Reference(
                Httpresource::rangeable(
                    'foobar',
                    new Gateway('bar'),
                    new IdentityDefinition('foo'),
                    new Set(Property::class)
                ),
                new Identity('foo')
            )
        );

        $this->assertSame(
            [$foo, $bar],
            $headers->toPrimitive()
        );
    }
}
