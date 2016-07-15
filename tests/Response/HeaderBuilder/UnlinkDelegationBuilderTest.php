<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\UnlinkDelegationBuilder,
    Response\HeaderBuilder\UnlinkBuilderInterface,
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
    Collection,
    MapInterface
};

class UnlinkDelegationBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $builder = new UnlinkDelegationBuilder(
            new Set(UnlinkBuilderInterface::class)
        );

        $this->assertInstanceOf(UnlinkBuilderInterface::class, $builder);
        $headers = $builder->build(
            $this->createMock(ServerRequestInterface::class),
            new Reference(
                new Httpresource(
                    'foobar',
                    new IdentityDefinition('foo'),
                    new Map('string', Property::class),
                    new Collection([]),
                    new Collection([]),
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
        new UnlinkDelegationBuilder(new Set('object'));
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidTos()
    {
        $builder = new UnlinkDelegationBuilder(
            new Set(UnlinkBuilderInterface::class)
        );

        $builder->build(
            $this->createMock(ServerRequestInterface::class),
            new Reference(
                new Httpresource(
                    'foobar',
                    new IdentityDefinition('foo'),
                    new Map('string', Property::class),
                    new Collection([]),
                    new Collection([]),
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
            (new Set(UnlinkBuilderInterface::class))
                ->add($mock1 = $this->createMock(UnlinkBuilderInterface::class))
                ->add($mock2 = $this->createMock(UnlinkBuilderInterface::class))
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
                    new Collection([]),
                    new Collection([]),
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
