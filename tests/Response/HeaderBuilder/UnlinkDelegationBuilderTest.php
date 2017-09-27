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
    Definition\Gateway
};
use Innmind\Http\{
    Message\ServerRequest,
    Header
};
use Innmind\Immutable\{
    Set,
    Map,
    Collection,
    MapInterface
};
use PHPUnit\Framework\TestCase;

class UnlinkDelegationBuilderTest extends TestCase
{
    public function testInterface()
    {
        $builder = new UnlinkDelegationBuilder(
            new Set(UnlinkBuilder::class)
        );

        $this->assertInstanceOf(UnlinkBuilder::class, $builder);
        $headers = $builder->build(
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
     * @expectedExceptionMessage Argument 1 must be of type SetInterface<Innmind\Rest\Server\Response\HeaderBuilder\UnlinkBuilder>
     */
    public function testThrowWhenInvalidBuilderSet()
    {
        new UnlinkDelegationBuilder(new Set('object'));
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 3 must be of type MapInterface<Innmind\Rest\Server\Reference, Innmind\Immutable\MapInterface>
     */
    public function testThrowWhenInvalidTos()
    {
        $builder = new UnlinkDelegationBuilder(
            new Set(UnlinkBuilder::class)
        );

        $builder->build(
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
        $builder = new UnlinkDelegationBuilder(
            (new Set(UnlinkBuilder::class))
                ->add($mock1 = $this->createMock(UnlinkBuilder::class))
                ->add($mock2 = $this->createMock(UnlinkBuilder::class))
        );
        $mock1
            ->method('build')
            ->willReturn(
                (new Map('string', Header::class))
                    ->put('foo', $this->createMock(Header::class))
            );
        $mock2
            ->method('build')
            ->willReturn(
                (new Map('string', Header::class))
                    ->put('bar', $this->createMock(Header::class))
            );

        $headers = $builder->build(
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
