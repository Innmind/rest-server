<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\LinkDelegationBuilder,
    Response\HeaderBuilder\LinkBuilderInterface,
    Identity,
    Reference,
    Definition\Httpresource,
    Definition\Identity as IdentityDefinition,
    Definition\Property,
    Definition\Gateway
};
use Innmind\Http\{
    Message\ServerRequestInterface,
    Header\HeaderInterface
};
use Innmind\Immutable\{
    Set,
    Map,
    MapInterface
};

class LinkDelegationBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $builder = new LinkDelegationBuilder(
            new Set(LinkBuilderInterface::class)
        );

        $this->assertInstanceOf(LinkBuilderInterface::class, $builder);
        $headers = $builder->build(
            $this->createMock(ServerRequestInterface::class),
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
        $this->assertSame(HeaderInterface::class, (string) $headers->valueType());
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidBuilderSet()
    {
        new LinkDelegationBuilder(new Set('object'));
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidTos()
    {
        $builder = new LinkDelegationBuilder(
            new Set(LinkBuilderInterface::class)
        );

        $builder->build(
            $this->createMock(ServerRequestInterface::class),
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
        $builder = new LinkDelegationBuilder(
            (new Set(LinkBuilderInterface::class))
                ->add($mock1 = $this->createMock(LinkBuilderInterface::class))
                ->add($mock2 = $this->createMock(LinkBuilderInterface::class))
        );
        $mock1
            ->method('build')
            ->willReturn(
                (new Map('string', HeaderInterface::class))
                    ->put('foo', $this->createMock(HeaderInterface::class))
            );
        $mock2
            ->method('build')
            ->willReturn(
                (new Map('string', HeaderInterface::class))
                    ->put('bar', $this->createMock(HeaderInterface::class))
            );

        $headers = $builder->build(
            $this->createMock(ServerRequestInterface::class),
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
