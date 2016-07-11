<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\RemoveDelegationBuilder,
    Response\HeaderBuilder\RemoveBuilderInterface,
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

class RemoveDelegationBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $builder = new RemoveDelegationBuilder(
            new Set(RemoveBuilderInterface::class)
        );

        $this->assertInstanceOf(RemoveBuilderInterface::class, $builder);
        $headers = $builder->build(
            $this->createMock(ServerRequestInterface::class),
            new Httpresource(
                'foobar',
                new Identity('foo'),
                new Map('string', Property::class),
                new Collection([]),
                new Collection([]),
                new Gateway('bar'),
                true
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
        new RemoveDelegationBuilder(new Set('object'));
    }

    public function testBuild()
    {
        $builder = new RemoveDelegationBuilder(
            (new Set(RemoveBuilderInterface::class))
                ->add($mock1 = $this->createMock(RemoveBuilderInterface::class))
                ->add($mock2 = $this->createMock(RemoveBuilderInterface::class))
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
            new Httpresource(
                'foobar',
                new Identity('foo'),
                new Map('string', Property::class),
                new Collection([]),
                new Collection([]),
                new Gateway('bar'),
                true
            ),
            $this->createMock(IdentityInterface::class)
        );

        $this->assertSame(
            ['foo', 'bar'],
            $headers->keys()->toPrimitive()
        );
    }
}
