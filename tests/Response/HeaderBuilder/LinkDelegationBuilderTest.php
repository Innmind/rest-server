<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\LinkDelegationBuilder,
    Response\HeaderBuilder\LinkBuilder,
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
use Innmind\Immutable\Set;
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class LinkDelegationBuilderTest extends TestCase
{
    public function testInterface()
    {
        $build = new LinkDelegationBuilder;

        $this->assertInstanceOf(LinkBuilder::class, $build);
        $headers = $build(
            $this->createMock(ServerRequest::class),
            new Reference(
                Httpresource::rangeable(
                    'foobar',
                    new Gateway('bar'),
                    new IdentityDefinition('foo'),
                    Set::of(Property::class)
                ),
                new Identity('foo')
            )
        );
        $this->assertInstanceOf(Set::class, $headers);
        $this->assertSame(Header::class, (string) $headers->type());
    }

    public function testBuild()
    {
        $build = new LinkDelegationBuilder(
            $mock1 = $this->createMock(LinkBuilder::class),
            $mock2 = $this->createMock(LinkBuilder::class)
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
                    Set::of(Property::class)
                ),
                new Identity('foo')
            )
        );

        $this->assertSame(
            [$foo, $bar],
            unwrap($headers),
        );
    }
}
