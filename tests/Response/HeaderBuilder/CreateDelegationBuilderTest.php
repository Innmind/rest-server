<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\CreateDelegationBuilder,
    Response\HeaderBuilder\CreateBuilder,
    Identity as IdentityInterface,
    Definition\Httpresource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway,
    HttpResource as HttpResourceInterface,
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

class CreateDelegationBuilderTest extends TestCase
{
    public function testInterface()
    {
        $build = new CreateDelegationBuilder;

        $this->assertInstanceOf(CreateBuilder::class, $build);
        $headers = $build(
            $this->createMock(IdentityInterface::class),
            $this->createMock(ServerRequest::class),
            Httpresource::rangeable(
                'foobar',
                new Gateway('bar'),
                new Identity('foo'),
                new Set(Property::class)
            ),
            $this->createMock(HttpResourceInterface::class)
        );
        $this->assertInstanceOf(SetInterface::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
    }

    public function testBuild()
    {
        $build = new CreateDelegationBuilder(
            $mock1 = $this->createMock(CreateBuilder::class),
            $mock2 = $this->createMock(CreateBuilder::class)
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
            $this->createMock(IdentityInterface::class),
            $this->createMock(ServerRequest::class),
            Httpresource::rangeable(
                'foobar',
                new Gateway('bar'),
                new Identity('foo'),
                new Set(Property::class)
            ),
            $this->createMock(HttpResourceInterface::class)
        );

        $this->assertSame(
            [$foo, $bar],
            $headers->toPrimitive()
        );
    }
}
