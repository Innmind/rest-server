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
    Map,
    MapInterface,
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
                new Httpresource(
                    'foobar',
                    new IdentityDefinition('foo'),
                    new Map('string', Property::class),
                    new Map('scalar', 'variable'),
                    new Map('scalar', 'variable'),
                    new Gateway('bar'),
                    true,
                    new Map('string', 'string')
                ),
                new Identity('foo')
            ),
            new Map(Reference::class, MapInterface::class)
        );
        $this->assertInstanceOf(MapInterface::class, $headers);
        $this->assertSame('string', (string) $headers->keyType());
        $this->assertSame(Header::class, (string) $headers->valueType());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 3 must be of type MapInterface<Innmind\Rest\Server\Reference, Innmind\Immutable\MapInterface>
     */
    public function testThrowWhenInvalidTos()
    {
        $build = new UnlinkDelegationBuilder;

        $build(
            $this->createMock(ServerRequest::class),
            new Reference(
                new Httpresource(
                    'foobar',
                    new IdentityDefinition('foo'),
                    new Map('string', Property::class),
                    new Map('scalar', 'variable'),
                    new Map('scalar', 'variable'),
                    new Gateway('bar'),
                    true,
                    new Map('string', 'string')
                ),
                new Identity('foo')
            ),
            new Map('string', 'string')
        );
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
                (new Map('string', Header::class))
                    ->put('foo', $this->createMock(Header::class))
            );
        $mock2
            ->method('__invoke')
            ->willReturn(
                (new Map('string', Header::class))
                    ->put('bar', $this->createMock(Header::class))
            );

        $headers = $build(
            $this->createMock(ServerRequest::class),
            new Reference(
                new Httpresource(
                    'foobar',
                    new IdentityDefinition('foo'),
                    new Map('string', Property::class),
                    new Map('scalar', 'variable'),
                    new Map('scalar', 'variable'),
                    new Gateway('bar'),
                    true,
                    new Map('string', 'string')
                ),
                new Identity('foo')
            ),
            new Map(Reference::class, MapInterface::class)
        );

        $this->assertSame(
            ['foo', 'bar'],
            $headers->keys()->toPrimitive()
        );
    }
}
