<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\GetDelegationBuilder,
    Response\HeaderBuilder\GetBuilderInterface,
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

class GetDelegationBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $builder = new GetDelegationBuilder(
            new Set(GetBuilderInterface::class)
        );

        $this->assertInstanceOf(GetBuilderInterface::class, $builder);
        $headers = $builder->build(
            $this->createMock(HttpResourceInterface::class),
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
            $this->createMock(IdentityInterface::class)
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
        new GetDelegationBuilder(new Set('object'));
    }

    public function testBuild()
    {
        $builder = new GetDelegationBuilder(
            (new Set(GetBuilderInterface::class))
                ->add($mock1 = $this->createMock(GetBuilderInterface::class))
                ->add($mock2 = $this->createMock(GetBuilderInterface::class))
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
            $this->createMock(HttpResourceInterface::class),
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
            $this->createMock(IdentityInterface::class)
        );

        $this->assertSame(
            ['foo', 'bar'],
            $headers->keys()->toPrimitive()
        );
    }
}
