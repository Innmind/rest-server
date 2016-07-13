<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\ListDelegationBuilder,
    Response\HeaderBuilder\ListBuilderInterface,
    IdentityInterface,
    Definition\Httpresource,
    Definition\Identity,
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

class ListDelegationBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $builder = new ListDelegationBuilder(
            new Set(ListBuilderInterface::class)
        );

        $this->assertInstanceOf(ListBuilderInterface::class, $builder);
        $headers = $builder->build(
            new Set(IdentityInterface::class),
            $this->createMock(ServerRequestInterface::class),
            new Httpresource(
                'foobar',
                new Identity('foo'),
                new Map('string', Property::class),
                new Collection([]),
                new Collection([]),
                new Gateway('bar'),
                true,
                new Map('string', 'string')
            )
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
        new ListDelegationBuilder(new Set('object'));
    }

    /**
     * @expectedException Innmind\Rest\Server\Exception\InvalidArgumentException
     */
    public function testThrowWhenInvalidIdentities()
    {
        $builder = new ListDelegationBuilder(
            new Set(ListBuilderInterface::class)
        );

        $builder->build(
            new Set('object'),
            $this->createMock(ServerRequestInterface::class),
            new Httpresource(
                'foobar',
                new Identity('foo'),
                new Map('string', Property::class),
                new Collection([]),
                new Collection([]),
                new Gateway('bar'),
                true,
                new Map('string', 'string')
            )
        );
    }

    public function testBuild()
    {
        $builder = new ListDelegationBuilder(
            (new Set(ListBuilderInterface::class))
                ->add($mock1 = $this->createMock(ListBuilderInterface::class))
                ->add($mock2 = $this->createMock(ListBuilderInterface::class))
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
            new Set(IdentityInterface::class),
            $this->createMock(ServerRequestInterface::class),
            new Httpresource(
                'foobar',
                new Identity('foo'),
                new Map('string', Property::class),
                new Collection([]),
                new Collection([]),
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
