<?php
declare(strict_types = 1);

namespace Tests\Innmind\Rest\Server\Response\HeaderBuilder;

use Innmind\Rest\Server\{
    Response\HeaderBuilder\CreateDelegationBuilder,
    Response\HeaderBuilder\CreateBuilder,
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
    Map,
    MapInterface
};
use PHPUnit\Framework\TestCase;

class CreateDelegationBuilderTest extends TestCase
{
    public function testInterface()
    {
        $build = new CreateDelegationBuilder;

        $this->assertInstanceOf(CreateBuilder::class, $build);
        $headers = $build(
            $this->createMock(IdentityInterface::class),
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
            $this->createMock(HttpResourceInterface::class)
        );
        $this->assertInstanceOf(MapInterface::class, $headers);
        $this->assertSame('string', (string) $headers->keyType());
        $this->assertSame(Header::class, (string) $headers->valueType());
    }

    public function testBuild()
    {
        $build = new CreateDelegationBuilder(
            $mock1 = $this->createMock(CreateBuilder::class),
            $mock2 = $this->createMock(CreateBuilder::class)
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
            $this->createMock(IdentityInterface::class),
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
            $this->createMock(HttpResourceInterface::class)
        );

        $this->assertSame(
            ['foo', 'bar'],
            $headers->keys()->toPrimitive()
        );
    }
}
