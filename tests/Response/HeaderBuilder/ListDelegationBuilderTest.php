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
    Definition\Gateway
};
use Innmind\Http\{
    Message\ServerRequest,
    Header
};
use Innmind\Immutable\{
    Set,
    Map,
    MapInterface
};
use PHPUnit\Framework\TestCase;

class ListDelegationBuilderTest extends TestCase
{
    public function testInterface()
    {
        $builder = new ListDelegationBuilder(
            new Set(ListBuilder::class)
        );

        $this->assertInstanceOf(ListBuilder::class, $builder);
        $headers = $builder->build(
            new Set(IdentityInterface::class),
            $this->createMock(ServerRequest::class),
            new Httpresource(
                'foobar',
                new Identity('foo'),
                new Map('string', Property::class),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('bar'),
                true,
                new Map('string', 'string')
            )
        );
        $this->assertInstanceOf(MapInterface::class, $headers);
        $this->assertSame('string', (string) $headers->keyType());
        $this->assertSame(Header::class, (string) $headers->valueType());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 must be of SetInterface<Innmind\Rest\Server\Response\HeaderBuilder\ListBuilder>
     */
    public function testThrowWhenInvalidBuilderSet()
    {
        new ListDelegationBuilder(new Set('object'));
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 must be of type SetInterface<Innmind\Rest\Server\Identity>
     */
    public function testThrowWhenInvalidIdentities()
    {
        $builder = new ListDelegationBuilder(
            new Set(ListBuilder::class)
        );

        $builder->build(
            new Set('object'),
            $this->createMock(ServerRequest::class),
            new Httpresource(
                'foobar',
                new Identity('foo'),
                new Map('string', Property::class),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('bar'),
                true,
                new Map('string', 'string')
            )
        );
    }

    public function testBuild()
    {
        $builder = new ListDelegationBuilder(
            (new Set(ListBuilder::class))
                ->add($mock1 = $this->createMock(ListBuilder::class))
                ->add($mock2 = $this->createMock(ListBuilder::class))
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
            new Set(IdentityInterface::class),
            $this->createMock(ServerRequest::class),
            new Httpresource(
                'foobar',
                new Identity('foo'),
                new Map('string', Property::class),
                new Map('scalar', 'variable'),
                new Map('scalar', 'variable'),
                new Gateway('bar'),
                true,
                new Map('string', 'string')
            )
        );

        $this->assertSame(
            ['foo', 'bar'],
            $headers->keys()->toPrimitive()
        );
    }
}
