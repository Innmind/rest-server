<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\GetDelegationBuilder,
    Response\HeaderBuilder\GetBuilder,
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
use Innmind\Immutable\Set;
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class GetDelegationBuilderTest extends TestCase
{
    public function testInterface()
    {
        $build = new GetDelegationBuilder;

        $this->assertInstanceOf(GetBuilder::class, $build);
        $headers = $build(
            $this->createMock(HttpResourceInterface::class),
            $this->createMock(ServerRequest::class),
            Httpresource::rangeable(
                'foobar',
                new Gateway('bar'),
                new Identity('foo'),
                Set::of(Property::class)
            ),
            $this->createMock(IdentityInterface::class)
        );
        $this->assertInstanceOf(Set::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
    }

    public function testBuild()
    {
        $build = new GetDelegationBuilder(
            $mock1 = $this->createMock(GetBuilder::class),
            $mock2 = $this->createMock(GetBuilder::class)
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
            $this->createMock(HttpResourceInterface::class),
            $this->createMock(ServerRequest::class),
            Httpresource::rangeable(
                'foobar',
                new Gateway('bar'),
                new Identity('foo'),
                Set::of(Property::class)
            ),
            $this->createMock(IdentityInterface::class)
        );

        $this->assertSame(
            [$foo, $bar],
            unwrap($headers),
        );
    }
}
