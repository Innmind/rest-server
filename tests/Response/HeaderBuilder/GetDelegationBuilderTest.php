<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\GetDelegationBuilder,
    Response\HeaderBuilder\GetBuilder,
    Identity as IdentityInterface,
    Definition\Httpresource,
    Definition\Identity,
    Definition\Property,
    Definition\Gateway,
    HttpResource as HttpResourceInterface
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

class GetDelegationBuilderTest extends TestCase
{
    public function testInterface()
    {
        $builder = new GetDelegationBuilder(
            new Set(GetBuilder::class)
        );

        $this->assertInstanceOf(GetBuilder::class, $builder);
        $headers = $builder->build(
            $this->createMock(HttpResourceInterface::class),
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
            ),
            $this->createMock(IdentityInterface::class)
        );
        $this->assertInstanceOf(MapInterface::class, $headers);
        $this->assertSame('string', (string) $headers->keyType());
        $this->assertSame(Header::class, (string) $headers->valueType());
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
            (new Set(GetBuilder::class))
                ->add($mock1 = $this->createMock(GetBuilder::class))
                ->add($mock2 = $this->createMock(GetBuilder::class))
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
            $this->createMock(HttpResourceInterface::class),
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
            ),
            $this->createMock(IdentityInterface::class)
        );

        $this->assertSame(
            ['foo', 'bar'],
            $headers->keys()->toPrimitive()
        );
    }
}
