<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\CreateDelegationBuilder,
    Response\HeaderBuilder\CreateBuilderInterface,
    IdentityInterface,
    Definition\Httpresource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway,
    HttpResourceInterface
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

class CreateDelegationBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $builder = new CreateDelegationBuilder(
            new Set(CreateBuilderInterface::class)
        );

        $this->assertInstanceOf(CreateBuilderInterface::class, $builder);
        $headers = $builder->build(
            $this->createMock(IdentityInterface::class),
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
            ),
            $this->createMock(HttpResourceInterface::class)
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
        new CreateDelegationBuilder(new Set('object'));
    }

    public function testBuild()
    {
        $builder = new CreateDelegationBuilder(
            (new Set(CreateBuilderInterface::class))
                ->add($mock1 = $this->createMock(CreateBuilderInterface::class))
                ->add($mock2 = $this->createMock(CreateBuilderInterface::class))
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
            $this->createMock(IdentityInterface::class),
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
            ),
            $this->createMock(HttpResourceInterface::class)
        );

        $this->assertSame(
            ['foo', 'bar'],
            $headers->keys()->toPrimitive()
        );
    }
}
